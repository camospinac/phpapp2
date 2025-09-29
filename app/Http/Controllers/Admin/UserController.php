<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class UserController extends Controller
{
    // Muestra la lista de todos los usuarios
    public function index()
    {
        $users = User::all();
        return Inertia::render('Admin/Users/Index', ['users' => $users]);
    }

    public function show(User $user)
    {
        // 1. Cargamos todas las relaciones necesarias en el objeto $user
        $userWithData = $user->load([
            'subscriptions.plan',
            'transactions' => fn($q) => $q->latest(),
            'rank' // Cargamos la relación del rango
        ]);

        // 2. Calculamos los totales para las tarjetas de resumen
        $totalInversion = $userWithData->subscriptions->sum('initial_investment');
        $totalProfit = $userWithData->subscriptions->sum('profit_amount');
        $totalGanancia = $totalInversion + $totalProfit;

        $abonos = $userWithData->transactions->where('tipo', 'abono')->sum('monto');
        $retiros = $userWithData->transactions->where('tipo', 'retiro')->sum('monto');
        $totalAvailable = $abonos - $retiros;

        // 3. Enviamos todo a la nueva vista de Vue
        return Inertia::render('Admin/Users/Show', [
            'user' => $userWithData, // Enviamos la versión con todos los datos cargados
            'stats' => [
                'totalInversion' => $totalInversion,
                'totalProfit' => $totalProfit,
                'totalGanancia' => $totalGanancia,
                'totalAvailable' => $totalAvailable,
            ]
        ]);
    }


    // Crea un nuevo usuario
    public function store(Request $request)
    {
        $request->validate([
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'celular' => 'required|string|max:20|unique:users',
            'email' => 'required|string|lowercase|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'rol' => 'required|in:admin,usuario',
        ]);

        User::create([
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'celular' => $request->celular,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => $request->rol,
        ]);

        return redirect()->route('admin.users.index');
    }

    public function update(Request $request, User $user)
    {
        // 1. Añadimos 'is_fraud' a la validación y 'sometimes' a los demás campos
        //    'sometimes' significa: valida este campo solo si está presente en la petición.
        $validated = $request->validate([
            'nombres' => 'sometimes|required|string|max:255',
            'apellidos' => 'sometimes|required|string|max:255',
            'celular' => 'sometimes|required|string|max:20|unique:users,celular,' . $user->id,
            'email' => 'sometimes|required|string|lowercase|email|max:255|unique:users,email,' . $user->id,
            'rol' => 'sometimes|required|in:admin,usuario',
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'is_fraud' => 'sometimes|boolean', // <-- La nueva regla
        ]);

        // 2. Preparamos los datos para actualizar
        //    Esto ahora incluye 'is_fraud' si fue enviado
        $updateData = Arr::except($validated, ['password']);

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // 3. Redirigimos a la página anterior para una mejor experiencia
        return back()->with('success', 'Usuario actualizado con éxito.');
    }
    // Elimina un usuario
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index');
    }

    public function blockAll(Request $request)
    {
        // 1. Definimos la frase de confirmación exacta
        $confirmationPhrase = 'BLOQUEAR A TODOS LOS USUARIOS';

        // 2. Validamos que el admin haya escrito la frase correctamente
        $request->validate([
            'confirmation' => ['required', 'string', 'in:' . $confirmationPhrase],
        ]);

        // 3. Si la validación pasa, ejecutamos la acción masiva
        //    Actualizamos solo a los que tienen el rol de 'usuario'
        User::where('rol', 'usuario')->update(['is_fraud' => true]);

        return redirect()->route('admin.users.index')
            ->with('success', '¡Acción de emergencia ejecutada! Todos los usuarios han sido bloqueados.');
    }
}
