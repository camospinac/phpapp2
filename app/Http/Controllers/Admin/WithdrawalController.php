<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
}