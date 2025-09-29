<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use App\Mail\TransferSentEmail;
use App\Mail\TransferReceivedEmail;

class TransferController extends Controller
{
    public function findByReferralCode(Request $request)
    {
        $request->validate([
            'referral_code' => ['required', 'string', 'exists:users,referral_code'],
        ]);

        $sender = Auth::user();
        $recipient = User::where('referral_code', $request->referral_code)->first();

        // Evitar que el usuario se busque a sí mismo
        if ($sender->id === $recipient->id) {
            return response()->json(['message' => 'No puedes transferir a tu propia cuenta.'], 422);
        }

        // Devolvemos solo los datos públicos necesarios
        return response()->json([
            'id' => $recipient->id,
            'nombres' => $recipient->nombres,
            'apellidos' => $recipient->apellidos,
        ]);
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $sender */
        $sender = Auth::user();

        // 1. Validate the incoming data
        $request->validate([
            'recipient_code' => 'required|string|exists:users,referral_code',
            'amount' => 'required|numeric|min:1',
            'password' => 'required|string',
        ]);

        // 2. Verify the sender's password
        if (!Hash::check($request->password, $sender->password)) {
            throw ValidationException::withMessages([
                'password' => 'La contraseña es incorrecta.',
            ]);
        }

        $recipient = User::where('referral_code', $request->recipient_code)->first();
        $amount = $request->amount;

        // 3. Perform additional security checks
        if ($sender->id === $recipient->id) {
            return back()->with('error', 'No puedes enviarte dinero a ti mismo.');
        }

        // 4. The main logic inside a database transaction
        DB::transaction(function () use ($sender, $recipient, $amount) {
            // Check sender's balance
            $balance = $sender->transactions()->where('tipo', 'abono')->sum('monto') - $sender->transactions()->where('tipo', 'retiro')->sum('monto');
            
            if ($balance < $amount) {
                throw ValidationException::withMessages([
                    'amount' => 'No tienes saldo suficiente para realizar esta transferencia.',
                ]);
            }

            // Create the two transaction records (double-entry)
            // Debit the sender
            $sender->transactions()->create([
                'tipo' => 'retiro',
                'monto' => $amount,
                'observacion' => "Envío de saldo a {$recipient->nombres} {$recipient->apellidos}",
            ]);

            // Credit the recipient
            $recipient->transactions()->create([
                'tipo' => 'abono',
                'monto' => $amount,
                'observacion' => "Recepción de saldo de {$sender->nombres} {$sender->apellidos}",
            ]);
        });
        
        Mail::to($sender->email)->send(new TransferSentEmail($sender, $recipient, $amount));

        Mail::to($recipient->email)->send(new TransferReceivedEmail($sender, $recipient, $amount));


        return redirect()->route('dashboard')->with('success', '¡Transferencia realizada con éxito!');
    }
}