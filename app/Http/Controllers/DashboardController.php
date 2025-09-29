<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon; 
use App\Models\Plan;
use App\Models\Transaction;
use App\Models\Withdrawal; 
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    public function show()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->is_fraud) {
        return redirect()->route('account.blocked');
        }

        if ($user->rol === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // --- CÁLCULOS PRINCIPALES ---

        // 1. Obtenemos TODAS las suscripciones activas (con su plan para los cálculos)
        $activeSubscriptions = $user->subscriptions()
            ->with('plan')
            ->where('status', 'active')
            ->get();
        $withdrawals = $user->withdrawals()->latest()->get();

        // 2. Calculamos los TOTALES GLOBALES para las tarjetas
        $totalInversion = $activeSubscriptions->sum('initial_investment');
        $totalUtilidad = 0;
        
        // --- INICIA LA LÓGICA CORREGIDA ---
        // Iteramos sobre cada suscripción para calcular su utilidad específica
        foreach ($activeSubscriptions as $sub) {
            // Si el contrato es CERRADO, usamos el porcentaje para cerrados (40%)
            if ($sub->contract_type === 'cerrada' && $sub->plan->closed_profit_percentage) {
                $baseProfit = $sub->initial_investment * ($sub->plan->closed_profit_percentage / 100);
                $totalUtilidad += $baseProfit * 3; // La fórmula que definimos
            } 
            // Si el contrato es ABIERTO, usamos el porcentaje para abiertos (15%)
            elseif ($sub->contract_type === 'abierta' && $sub->plan->fixed_percentage) {
                $baseProfit = $sub->initial_investment * ($sub->plan->fixed_percentage / 100);
                $totalUtilidad += $baseProfit * 6;
            }
        }
        // --- FIN DE LA LÓGICA CORREGIDA ---

        $totalGanancia = $totalInversion + $totalUtilidad;
        
        // 3. Calculamos el saldo disponible desde la tabla de transacciones
        $abonos = $user->transactions()->where('tipo', 'abono')->sum('monto');
        $retiros = $user->transactions()->where('tipo', 'retiro')->sum('monto');
        $totalAvailable = $abonos - $retiros;

        // 4. Preparamos los datos para enviar a la vista
        return Inertia::render('Dashboard', [
            // Cargamos los pagos aquí al final para mantener la consulta inicial ligera
            'subscriptions' => $activeSubscriptions->load(['payments' => function ($query) {
                $query->orderBy('payment_due_date', 'asc');
            }]),
            'plans' => Plan::all(),
            'transactions' => $user->transactions()->latest()->get(),
            'totalInversion' => $totalInversion,
            'totalUtilidad' => $totalUtilidad,
            'totalGanancia' => $totalGanancia,
            'totalAvailable' => $totalAvailable,
            'withdrawals' => $withdrawals, 
        ]);
    }

    public function downloadStatement()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Recopilamos toda la información del usuario
        $user->load(['subscriptions.plan', 'transactions' => fn($q) => $q->latest()]);

        // 2. Calculamos los totales
        $stats = [
            'totalInversion' => $user->subscriptions->sum('initial_investment'),
            'totalProfit' => $user->subscriptions->sum('profit_amount'),
            'totalGanancia' => $user->subscriptions->sum('initial_investment') + $user->subscriptions->sum('profit_amount'),
            'totalAvailable' => $user->transactions->where('tipo', 'abono')->sum('monto') - $user->transactions->where('tipo', 'retiro')->sum('monto'),
        ];

        // 3. Generamos el PDF
        $pdf = PDF::loadView('pdf.statement', [
            'user' => $user,
            'stats' => $stats,
            'subscriptions' => $user->subscriptions,
            'transactions' => $user->transactions,
        ]);

        // 4. Lo enviamos al navegador para su descarga
        return $pdf->download('extracto-' . now()->format('Y-m-d') . '.pdf');
    }
}