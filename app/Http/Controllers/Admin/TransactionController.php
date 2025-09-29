<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    // Muestra todas las transacciones
    public function index()
    {
        // Carga la relación 'user' para evitar N+1 queries
        $transactions = Transaction::with('user')->latest()->get();
        // También necesitamos la lista de usuarios para el formulario de creación
        $users = User::where('rol', 'usuario')->get(['id', 'nombres', 'apellidos']);

        return Inertia::render('Admin/Transactions/Index', [
            'transactions' => $transactions,
            'users' => $users
        ]);
    }

    // Guarda una nueva transacción
    public function store(Request $request)
    {
        $request->validate([
            'id_user' => 'required|exists:users,id',
            'tipo' => 'required|in:abono,retiro',
            'monto' => 'required|numeric|min:0.01',
            'observacion' => 'nullable|string',
        ]);

        Transaction::create($request->all());
        return redirect()->route('admin.transactions.index');
    }

    // Actualiza una transacción
    public function update(Request $request, Transaction $transaction)
    {
        $request->validate([
            'tipo' => 'required|in:abono,retiro',
            'monto' => 'required|numeric|min:0.01',
            'observacion' => 'nullable|string',
        ]);

        $transaction->update($request->all());
        return redirect()->route('admin.transactions.index');
    }

    // Elimina una transacción
    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return redirect()->route('admin.transactions.index');
    }
}