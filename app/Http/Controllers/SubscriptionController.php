<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CreatesPaymentSchedules; // 1. Importa el Trait
use App\Models\Plan;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewSubscriptionEmail;
use App\Mail\SubscriptionPendingEmail;
use App\Models\Subscription;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    use CreatesPaymentSchedules; // 2. Usa el Trait

    public function switchToClosed(Subscription $subscription)
    {
        // 1. Double-check that the user owns this subscription
          if ($subscription->user_id !== Auth::id()) {
            abort(403);
        }


        // 2. Validate the 48-hour window
        if ($subscription->created_at->diffInHours(Carbon::now()) > 48) {
            return back()->with('error', 'El periodo para cambiar de contrato ha expirado.');
        }

        // 3. Inactivate the old subscription and its pending payments
        DB::transaction(function () use ($subscription) {
            $oldStatus = $subscription->status;
            $subscription->status = 'switched';
            $subscription->save();

            $subscription->payments()->where('status', 'pending')->update(['status' => 'cancelled']);

            // 4. Create the new "closed" subscription
            $newSubscription = $subscription->user->subscriptions()->create([
                'plan_id' => $subscription->plan_id,
                'sequence_id' => $subscription->user->subscriptions()->max('sequence_id') + 1,
                'initial_investment' => $subscription->initial_investment,
                'status' => $oldStatus, // Keep the original status (e.g., 'active')
                'contract_type' => 'cerrada',
            ]);

            // 5. Generate the new single payment
            $this->createPaymentSchedule($newSubscription);
        });

        return redirect()->route('dashboard')->with('success', 'Tu contrato ha sido cambiado a "Cerrado" exitosamente.');
    }


    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $lastSequence = $user->subscriptions()->max('sequence_id');
        $newSequenceId = $lastSequence + 1;

        // Lógica para calcular el saldo disponible (esto está perfecto)
        $abonos = $user->transactions()->where('tipo', 'abono')->sum('monto');
        $retiros = $user->transactions()->where('tipo', 'retiro')->sum('monto');
        $totalAvailable = $abonos - $retiros;

        // Lógica de validación (esto está perfecto)

        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'payment_method' => 'required|in:transfer,balance',
            'investment_contract_type' => 'required|in:abierta,cerrada',

            // Regla corregida para 'receipt'
            'receipt' => [
                Rule::requiredIf($request->payment_method === 'transfer'),
                'nullable', // Permite que el campo sea nulo si no es requerido
                'image',
                'max:2048',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:200000',
                function ($attribute, $value, $fail) use ($request, $totalAvailable) {
                    if ($request->payment_method === 'balance' && $value > $totalAvailable) {
                        $fail('El monto no puede ser mayor a tu saldo disponible.');
                    }
                },
            ],
        ]);

        $plan = Plan::findOrFail($request->plan_id);
        $newSubscription = null;

        DB::transaction(function () use ($user, $plan, $request, &$newSubscription, $newSequenceId) {
            $receiptPath = null;
            $status = 'pending_verification';

            // Lógica para el método de pago (esto está perfecto)
            if ($request->payment_method === 'balance') {
                $status = 'active';
                Transaction::create([
                    'id_user' => $user->id,
                    'tipo' => 'retiro',
                    'monto' => $request->amount,
                    'observacion' => "Reinvertido en nuevo plan: {$plan->name}",
                ]);
            } else {
                $status = 'pending_verification';
                if ($request->hasFile('receipt')) {
                    $receiptPath = $request->file('receipt')->store('receipts', 'public');
                }
            }

            // Creamos la nueva suscripción
            $newSubscription = $user->subscriptions()->create([
                'plan_id' => $plan->id,
                'initial_investment' => $request->amount,
                'status' => $status,
                'payment_receipt_path' => $receiptPath,
                'contract_type' => $request->investment_contract_type,
                'sequence_id' => $newSequenceId,
            ]);

            // 3. ¡AQUÍ ESTÁ LA MAGIA!
            // En lugar de todo el código duplicado, solo llamamos a nuestro "ayudante".
            $this->createPaymentSchedule($newSubscription);
        });

        if ($newSubscription) {
            // Enviamos la confirmación al usuario
            Mail::to($user->email)->send(new NewSubscriptionEmail($newSubscription));
            event(new \App\Events\NewInvestmentPending($newSubscription->load('user')));

            // Si está pendiente, notificamos al admin
            if ($newSubscription->status === 'pending_verification') {
                Mail::to('eongrupoempresarial@gmail.com')->send(new SubscriptionPendingEmail($newSubscription));
            }
        }

        return redirect()->route('dashboard')->with('success', '¡Tu nueva inversión ha sido creada con éxito!');
    }
}
