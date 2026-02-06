<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;

class WithdrawalController extends Controller
{
    /**
     * Muestra una lista de retiros pendientes, con opción de búsqueda.
     */
    public function index(Request $request)
    {
        $pendingWithdrawals = Withdrawal::with('user')
            ->where('status', 'pending')
            ->when($request->input('search'), function ($query, $search) {
                $query->where('code', $search);
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Admin/Withdrawals/Index', [
            'withdrawals' => $pendingWithdrawals,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Marca un retiro como completado.
     */
    public function complete(Withdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Esta solicitud de retiro ya fue procesada.');
        }

        $withdrawal->status = 'completed';
        $withdrawal->save();

        // (Futuro) Aquí podrías enviar un email de confirmación al usuario.

        return redirect()->route('admin.withdrawals.index')
            ->with('success', '¡Retiro marcado como completado!');
    }

    public function reject(Withdrawal $withdrawal)
    {
        // 1. Verificación de seguridad: solo rechazar si está pendiente
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Esta solicitud ya ha sido procesada.');
        }

        // 2. Iniciamos la transacción de Base de Datos
        DB::transaction(function () use ($withdrawal) {
            
            // 3. Cambiamos el estado del retiro a 'rejected'
            $withdrawal->update([
                'status' => 'rejected'
            ]);

            // 4. CREAMOS EL ABONO (La Reversa)
            // Usamos 'id_user' como lo tienes en tu lógica de Transaction
            Transaction::create([
                'id_user'     => $withdrawal->user_id, // El dueño del dinero
                'tipo'        => 'abono',              // Sumamos al saldo
                'monto'       => $withdrawal->amount,  // El mismo monto que se le quitó
                'observacion' => "Reversión por retiro rechazado (Código: {$withdrawal->code})",
            ]);
        });

        // 5. Redireccionamos con éxito
        return back()->with('success', 'Retiro rechazado. El dinero ha sido devuelto al saldo del usuario.');
    }
}