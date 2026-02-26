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
use App\Models\PaymentMethod;

class DashboardController extends Controller
{
    public function show()
    {
        $paymentMethods = PaymentMethod::where('is_active', true)
            ->get(['id', 'name', 'account_details', 'logo_path']);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->is_fraud) {
            return redirect()->route('account.blocked');
        }

        if ($user->rol === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // --- CÁLCULOS PRINCIPALES (Sincronizados con la DB) ---

        // 1. Obtenemos las suscripciones activas
        $activeSubscriptions = $user->subscriptions()
            ->with('plan')
            ->where('status', 'active')
            ->get();

        // 2. TOTALES PARA LAS TARJETAS: Leemos directamente de la base de datos
        // Ya no hacemos foreach, confiamos en lo que guardó el Trait y el Comando
        $totalInversion = $activeSubscriptions->sum('initial_investment');
        $totalUtilidad  = $activeSubscriptions->sum('profit_amount'); // 👈 Aquí está la clave
        $totalGanancia   = $totalInversion + $totalUtilidad;

        // 3. Saldo Disponible (Transacciones)
        $withdrawals = $user->withdrawals()->latest()->get();
        $abonos = $user->transactions()->where('tipo', 'abono')->sum('monto');
        $retiros = $user->transactions()->where('tipo', 'retiro')->sum('monto');
        $totalAvailable = $abonos - $retiros;

        return Inertia::render('Dashboard', [
            'subscriptions' => $activeSubscriptions->load(['payments' => function ($query) {
                $query->orderBy('payment_due_date', 'asc');
            }]),
            'plans' => Plan::all(),
            'transactions' => $user->transactions()->latest()->get(),
            'totalInversion' => $totalInversion,
            'totalUtilidad'  => $totalUtilidad,
            'totalGanancia'  => $totalGanancia,
            'totalAvailable' => $totalAvailable,
            'withdrawals'    => $withdrawals, 
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function downloadStatement()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Cargamos relaciones
        $user->load(['subscriptions.plan', 'transactions' => fn($q) => $q->latest()]);

        // Sincronizamos los stats del PDF con la misma lógica del Dashboard
        $totalInversion = $user->subscriptions->where('status', 'active')->sum('initial_investment');
        $totalProfit    = $user->subscriptions->where('status', 'active')->sum('profit_amount');

        $stats = [
            'totalInversion' => $totalInversion,
            'totalProfit'    => $totalProfit,
            'totalGanancia'  => $totalInversion + $totalProfit,
            'totalAvailable' => $user->transactions->where('tipo', 'abono')->sum('monto') - 
                                $user->transactions->where('tipo', 'retiro')->sum('monto'),
        ];

        $pdf = PDF::loadView('pdf.statement', [
            'user' => $user,
            'stats' => $stats,
            'subscriptions' => $user->subscriptions,
            'transactions' => $user->transactions,
        ]);

        return $pdf->download('extracto-' . now()->format('Y-m-d') . '.pdf');
    }
}