<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DashboardController extends Controller
{
    public function index(Request $request) // 👈 Añadimos Request para los filtros
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        // Filtro común para las 3 consultas
        $filter = function ($query) use ($dateFrom, $dateTo) {
            if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
            if ($dateTo)   $query->whereDate('created_at', '<=', $dateTo);
        };

        // Aplicamos el filtro a cada uno
        $subs = Subscription::with('user')->where($filter)->latest()->get();
        $withdraws = Withdrawal::with('user')->where($filter)->latest()->get();
        $registrations = User::where('rol', 'usuario')->where($filter)->latest()->get();

        // 2. ESTADÍSTICAS (Mantenemos tu lógica pero optimizada)
        $stats = [
            'realUsers' => User::where('rol', 'usuario')->where('es_cuenta_prueba', false)->count(),
            'testUsers' => User::where('rol', 'usuario')->where('es_cuenta_prueba', true)->count(),
            'activeRealSubscriptions' => Subscription::where('status', 'active')
                ->whereHas('user', fn($q) => $q->where('es_cuenta_prueba', false))->count(),
            'activeTestSubscriptions' => Subscription::where('status', 'active')
                ->whereHas('user', fn($q) => $q->where('es_cuenta_prueba', true))->count(),
            'pendingSubscriptions' => Subscription::where('status', 'pending_verification')->count(),
            'pendingWithdrawalsValue' => Withdrawal::where('status', 'pending')->sum('amount'),
        ];

        // 3. OBTENER ACTIVIDAD (Suscripciones, Retiros y Registros)

        // A. Suscripciones
        $subs = Subscription::with('user')->latest()
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->get();

        // B. Retiros
        $withdraws = Withdrawal::with('user')->latest()
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->get();

        // C. Registros Nuevos (Los que querías añadir)
        $registrations = User::where('rol', 'usuario')->latest()
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->get();

        // 4. UNIFICAR Y MAPEAR
        $mergedActivity = collect()
            ->concat($subs->map(fn($item) => [
                'type' => 'Suscripción',
                'user_id' => $item->user_id, // Para el link al perfil
                'user_name' => ($item->user->nombres ?? 'Desconocido') . ' ' . ($item->user->apellidos ?? ''),
                'amount' => $item->initial_investment,
                'status' => $item->status,
                'date' => $item->created_at->diffForHumans(),
                'created_at' => $item->created_at,
            ]))
            ->concat($withdraws->map(fn($item) => [
                'type' => 'Retiro',
                'user_id' => $item->user_id,
                'user_name' => ($item->user->nombres ?? 'Desconocido') . ' ' . ($item->user->apellidos ?? ''),
                'amount' => $item->amount,
                'status' => $item->status,
                'date' => $item->created_at->diffForHumans(),
                'created_at' => $item->created_at,
            ]))
            ->concat($registrations->map(fn($item) => [
                'type' => 'Registro',
                'user_id' => $item->id,
                'user_name' => $item->nombres . ' ' . $item->apellidos, // 👈 Aquí NO uses $item->user->... porque $item ya es el usuario
                'amount' => 0,
                'status' => 'nuevo',
                'date' => $item->created_at->diffForHumans(),
                'created_at' => $item->created_at,
            ]))
            ->sortByDesc('created_at')
            ->values();

        // 5. PAGINACIÓN MANUAL (10 por página)
        $perPage = 10;
        $page = $request->input('page', 1);
        $paginatedActivity = new LengthAwarePaginator(
            $mergedActivity->forPage($page, $perPage),
            $mergedActivity->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );


        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentActivity' => $paginatedActivity,
            'filters' => $request->only(['date_from', 'date_to']),
        ]);
    }
}
