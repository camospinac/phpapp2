<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $realUsers = User::where('rol', 'usuario')
            ->where('es_cuenta_prueba', false)
            ->count();

        // Usuarios de Prueba (es_cuenta_prueba = 1)
        $testUsers = User::where('rol', 'usuario')
            ->where('es_cuenta_prueba', true)
            ->count();
$activeRealSubscriptions = Subscription::where('status', 'active')
        ->whereHas('user', function($query) {
            $query->where('es_cuenta_prueba', false);
        })->count();

    // Planes Activos - Usuarios de PRUEBA
    $activeTestSubscriptions = Subscription::where('status', 'active')
        ->whereHas('user', function($query) {
            $query->where('es_cuenta_prueba', true);
        })->count();

    $pendingSubscriptions = Subscription::where('status', 'pending_verification')->count();
    $pendingWithdrawalsValue = Withdrawal::where('status', 'pending')->sum('amount');
        $pendingSubscriptions = Subscription::where('status', 'pending_verification')->count();
        $pendingWithdrawalsValue = Withdrawal::where('status', 'pending')->sum('amount');

        // --- CÁLCULO PARA EL LOG DE ACTIVIDAD RECIENTE ---
        // Obtenemos las últimas 5 suscripciones y los últimos 5 retiros
        $recentSubscriptions = Subscription::with('user')->latest()->take(5)->get();
        $recentWithdrawals = Withdrawal::with('user')->latest()->take(5)->get();

        // Los unimos en una sola colección y los ordenamos por fecha de creación
        $recentActivity = $recentSubscriptions->concat($recentWithdrawals)
            ->sortByDesc('created_at')
            ->take(10) // Tomamos los 10 más recientes de la mezcla
            ->values() // Resetea los índices del array
            ->map(function ($item) {
                // Damos un formato unificado a cada item
                return [
                    'type' => $item instanceof Subscription ? 'Suscripción' : 'Retiro',
                    'user_name' => $item->user->nombres ?? 'Usuario Desconocido',
                    'amount' => $item instanceof Subscription ? $item->initial_investment : $item->amount,
                    'status' => $item->status,
                    'date' => $item->created_at->diffForHumans(),
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'realUsers' => $realUsers, // Mandamos el nuevo dato
                'testUsers' => $testUsers,
                'activeRealSubscriptions' => $activeRealSubscriptions, // Nuevo
            'activeTestSubscriptions' => $activeTestSubscriptions,
                
                'pendingSubscriptions' => $pendingSubscriptions,
                'pendingWithdrawalsValue' => $pendingWithdrawalsValue,
            ],
            'recentActivity' => $recentActivity,
        ]);
    }
}
