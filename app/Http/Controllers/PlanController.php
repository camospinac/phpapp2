<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CreatesPaymentSchedules;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail; // <-- 1. Añade los 'use'
use App\Mail\NewSubscriptionEmail;
use App\Mail\SubscriptionPendingEmail;

class PlanController extends Controller
{
    use CreatesPaymentSchedules;

    public function selection()
    {
        return Inertia::render('Plan/Selection', [
            'plans' => Plan::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'amount' => 'required|numeric|min:200000',
            'receipt' => 'required|image|max:2048',
            'investment_contract_type' => 'required|in:abierta,cerrada',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $plan = Plan::findOrFail($request->plan_id);
        $lastSequence = $user->subscriptions()->max('sequence_id');
        $newSequenceId = $lastSequence + 1;
        
        // 2. Declaramos la variable para "atrapar" la suscripción
        $newSubscription = null;

        DB::transaction(function () use ($user, $plan, $request, &$newSubscription, $newSequenceId) {
            // 3. Pasamos la variable por referencia
            
            $receiptPath = null;
            if ($request->hasFile('receipt')) {
                $receiptPath = $request->file('receipt')->store('receipts', 'public');
            }

            // 4. Atrapamos la nueva suscripción
            $newSubscription = $user->subscriptions()->create([
                'plan_id' => $plan->id,
                'initial_investment' => $request->amount,
                'status' => 'pending_verification',
                'payment_receipt_path' => $receiptPath,
                'contract_type' => $request->investment_contract_type,
                'sequence_id' => $newSequenceId,
            ]);

            $this->createPaymentSchedule($newSubscription);
        });

        // 5. Enviamos los correos DESPUÉS de que la transacción fue exitosa
        if ($newSubscription) {
            // Enviamos la confirmación al usuario
            Mail::to($user->email)->send(new NewSubscriptionEmail($newSubscription));
            
            // Si está pendiente, notificamos al admin
            if ($newSubscription->status === 'pending_verification') {
                Mail::to('eongrupoempresarial@gmail.com')->send(new SubscriptionPendingEmail($newSubscription));
            }
        }

        return redirect()->route('dashboard')->with('success', '¡Gracias! Tu plan está en proceso de verificación.');
    }
}