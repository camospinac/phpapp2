<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Winner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class WinnerController extends Controller
{
    public function index()
    {
        $winners = Winner::with('user')->latest()->paginate(10);
        return Inertia::render('Admin/Winner/Index', ['winners' => $winners]);
    }

    public function create()
    {
        $users = User::where('rol', 'usuario')->orderBy('nombres')->get(['id', 'nombres', 'apellidos']);
        return Inertia::render('Admin/Winner/Create', ['users' => $users]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'win_date' => 'required|date',
            'prize' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'photo' => 'required|image|max:2048',
        ]);

        $path = $request->file('photo')->store('winners', 'public');

        Winner::create([
            'user_id' => $validated['user_id'],
            'win_date' => $validated['win_date'],
            'prize' => $validated['prize'],
            'city' => $validated['city'],
            'photo_path' => $path,
        ]);

        return redirect()->route('admin.winners.index')->with('success', 'Ganador registrado con éxito.');
    }

    public function edit(Winner $winner)
    {
        $users = User::where('rol', 'usuario')->orderBy('nombres')->get(['id', 'nombres', 'apellidos']);
        return Inertia::render('Admin/Winner/Edit', [
            'winner' => $winner,
            'users' => $users,
        ]);
    }

    public function update(Request $request, Winner $winner)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'win_date' => 'required|date',
            'prize' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048', // Photo is optional on update
        ]);

        $path = $winner->photo_path;
        if ($request->hasFile('photo')) {
            // Delete the old photo if it exists
            if ($winner->photo_path) {
                Storage::disk('public')->delete($winner->photo_path);
            }
            $path = $request->file('photo')->store('winners', 'public');
        }

        $winner->update([
            'user_id' => $validated['user_id'],
            'win_date' => $validated['win_date'],
            'prize' => $validated['prize'],
            'city' => $validated['city'],
            'photo_path' => $path,
        ]);

        return redirect()->route('admin.winners.index')->with('success', 'Ganador actualizado con éxito.');
    }

    public function destroy(Winner $winner)
    {
        // Delete the photo from storage
        if ($winner->photo_path) {
            Storage::disk('public')->delete($winner->photo_path);
        }
        
        $winner->delete();

        return redirect()->route('admin.winners.index')->with('success', 'Ganador eliminado con éxito.');
    }
}