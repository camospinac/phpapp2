<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
// use App\Models\User; // Ya no lo necesitamos
use App\Models\Winner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class WinnerController extends Controller
{
    /**
     * Muestra la lista de ganadores.
     * Eliminamos la carga 'with('user')' porque ya no existe la relación.
     */
    public function index()
    {
        $winners = Winner::latest()->paginate(10); // <-- CAMBIO: Eliminado with('user')
        return Inertia::render('Admin/Winner/Index', ['winners' => $winners]);
    }

    /**
     * Muestra el formulario de creación.
     * Ya no necesitamos pasar la lista de usuarios.
     */
    public function create()
    {
        // <-- CAMBIO: Eliminada la consulta de Users
        return Inertia::render('Admin/Winner/Create');
    }

    /**
     * Almacena un nuevo ganador.
     * Validamos 'nombre_completo' y 'cedula' en lugar de 'user_id'.
     */
    public function store(Request $request)
    {
        // <-- CAMBIO: Actualizada la validación
        $validated = $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'cedula' => 'required|string|max:20',
            'win_date' => 'required|date',
            'prize' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'photo' => 'required|image|max:2048',
        ]);

        $path = $request->file('photo')->store('winners', 'public');

        // <-- CAMBIO: Actualizados los campos de creación
        Winner::create([
            'nombre_completo' => $validated['nombre_completo'],
            'cedula' => $validated['cedula'],
            'win_date' => $validated['win_date'],
            'prize' => $validated['prize'],
            'city' => $validated['city'],
            'photo_path' => $path,
        ]);

        return redirect()->route('admin.winners.index')->with('success', 'Ganador registrado con éxito.');
    }

    /**
     * Muestra el formulario de edición.
     * Ya no necesitamos pasar la lista de usuarios.
     */
    public function edit(Winner $winner)
    {
        // <-- CAMBIO: Eliminada la consulta de Users
        return Inertia::render('Admin/Winner/Edit', [
            'winner' => $winner,
            // 'users' => $users, // Ya no se pasa
        ]);
    }

    /**
     * Actualiza un ganador existente.
     * Validamos 'nombre_completo' y 'cedula' en lugar de 'user_id'.
     */
    public function update(Request $request, Winner $winner)
    {
        // <-- CAMBIO: Actualizada la validación
        $validated = $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'cedula' => 'required|string|max:20',
            'win_date' => 'required|date',
            'prize' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
        ]);

        $path = $winner->photo_path;
        if ($request->hasFile('photo')) {
            if ($winner->photo_path) {
                Storage::disk('public')->delete($winner->photo_path);
            }
            $path = $request->file('photo')->store('winners', 'public');
        }

        // <-- CAMBIO: Actualizados los campos de actualización
        $winner->update([
            'nombre_completo' => $validated['nombre_completo'],
            'cedula' => $validated['cedula'],
            'win_date' => $validated['win_date'],
            'prize' => $validated['prize'],
            'city' => $validated['city'],
            'photo_path' => $path,
        ]);

        return redirect()->route('admin.winners.index')->with('success', 'Ganador actualizado con éxito.');
    }

    /**
     * Elimina un ganador.
     * Este método no necesita cambios.
     */
    public function destroy(Winner $winner)
    {
        if ($winner->photo_path) {
            Storage::disk('public')->delete($winner->photo_path);
        }
        
        $winner->delete();

        return redirect()->route('admin.winners.index')->with('success', 'Ganador eliminado con éxito.');
    }
}