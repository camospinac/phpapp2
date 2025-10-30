<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use App\Mail\WithdrawalCodeEmail;
use App\Mail\WithdrawalPendingEmail;

class WithdrawalController extends Controller
{
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // Calculamos el saldo disponible del usuario
        $abonos = $user->transactions()->where('tipo', 'abono')->sum('monto');
        $retiros = $user->transactions()->where('tipo', 'retiro')->sum('monto');
        $totalAvailable = $abonos - $retiros;
        $paymentMethods = ['ZELLE', 'MOVI', 'NEQUI', 'DAVIPLATA', 'TRANSFIYA'];

        // Validamos que el monto sea válido y no exceda el saldo
        $request->validate([
            'amount' => ['required', 'numeric', 'min:1', "max:{$totalAvailable}"],
            'payment_method' => ['required', Rule::in($paymentMethods)],
            'destination_phone_number' => 'required|string|min:7|max:15',
        ]);

        // 1. Declaramos la variable que guardará el nuevo retiro
        $newWithdrawal = null;

        DB::transaction(function () use ($user, $request, &$newWithdrawal) {
            // 2. Pasamos la variable por referencia para poder modificarla dentro
            
            // Generamos un código único de 6 dígitos
            do {
                $newCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            } while (Withdrawal::where('code', $newCode)->exists());

            // 3. ¡La corrección clave! Guardamos el nuevo retiro en la variable
            $newWithdrawal = Withdrawal::create([
                'user_id' => $user->id,
                'code' => $newCode,
                'amount' => $request->amount,
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'destination_phone_number' => $request->destination_phone_number,
            ]);

            // Creamos la transacción de 'retiro' para descontar el saldo inmediatamente
            Transaction::create([
                'id_user' => $user->id,
                'tipo' => 'retiro',
                'monto' => $request->amount,
                'observacion' => "Solicitud de retiro con código: {$newCode}",
            ]);
        });

        // 4. Ahora la variable sí existe y este bloque se ejecuta
        if ($newWithdrawal) {
            // Enviamos el código al usuario
            Mail::to($user->email)->send(new WithdrawalCodeEmail($newWithdrawal));

            // Notificamos al admin
            Mail::to('eongrupoempresarial@gmail.com')->send(new WithdrawalPendingEmail($newWithdrawal));

            // Usamos ->load('user') para asegurarnos de que los datos del usuario vayan incluidos
            event(new \App\Events\NewWithdrawalRequest($newWithdrawal->load('user')));
        }

        // 5. Usamos el código del objeto para ser consistentes
        return redirect()->route('dashboard')
            ->with('success', '¡Solicitud de retiro generada con éxito!')
            ->with('withdrawal_code', $newWithdrawal ? $newWithdrawal->code : null);
    }
}