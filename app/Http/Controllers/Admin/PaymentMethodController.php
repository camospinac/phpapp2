<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentMethodController extends Controller
{
    // Muestra la lista de métodos de pago
    public function index()
    {
        return Inertia::render('Admin/PaymentMethods/Index', [
            'paymentMethods' => PaymentMethod::all(),
        ]);
    }

    // Muestra el formulario para editar un método
    public function edit(PaymentMethod $paymentMethod)
    {
        return Inertia::render('Admin/PaymentMethods/Edit', [
            'paymentMethod' => $paymentMethod,
        ]);
    }

    // Actualiza el método de pago
    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $validated = $request->validate([
            'account_details' => 'required|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        $paymentMethod->update($validated);

        return redirect()->route('admin.payment-methods.index')
            ->with('success', 'Método de pago actualizado.');
    }
}