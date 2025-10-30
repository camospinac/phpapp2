<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Transaction;    // <--- Modelo de Transacciones
use App\Models\Subscription;  // <--- Modelo de Suscripciones
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialDashboardController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // --- 2. CONSULTAS CON FILTRO DE PRUEBA ---

        // Función de filtro reutilizable
        $filtroPrueba = function ($query) {
            // whereHas('user'...) asume que el modelo (Subscription, Transaction, Payment)
            // tiene una relación directa o indirecta llamada 'user'
            $query->where('es_cuenta_prueba', false);
        };

        // INGRESOS
        $total_in_transfers = Subscription::whereHas('user', $filtroPrueba)
            ->where('status', 'active')
            ->whereNotNull('payment_receipt_path')
            ->when($startDate, fn($q, $date) => $q->where('created_at', '>=', Carbon::parse($date)->startOfDay()))
            ->when($endDate, fn($q, $date) => $q->where('created_at', '<=', Carbon::parse($date)->endOfDay()))
            ->sum('initial_investment') ?? 0;

        $total_in_reinvestments = Subscription::whereHas('user', $filtroPrueba)
            ->where('status', 'active')
            ->whereNull('payment_receipt_path')
            ->when($startDate, fn($q, $date) => $q->where('created_at', '>=', Carbon::parse($date)->startOfDay()))
            ->when($endDate, fn($q, $date) => $q->where('created_at', '<=', Carbon::parse($date)->endOfDay()))
            ->sum('initial_investment') ?? 0;

        // EGRESOS (Asumiendo que Transaction tiene la relación 'user')
        $total_out_withdrawals = Transaction::whereHas('user', $filtroPrueba)
            ->where('tipo', 'retiro')
            ->when($startDate, fn($q, $date) => $q->where('created_at', '>=', Carbon::parse($date)->startOfDay()))
            ->when($endDate, fn($q, $date) => $q->where('created_at', '<=', Carbon::parse($date)->endOfDay()))
            ->sum('monto') ?? 0;

        $total_out_profits = Transaction::whereHas('user', $filtroPrueba)
            ->where('tipo', 'abono')
            ->when($startDate, fn($q, $date) => $q->where('created_at', '>=', Carbon::parse($date)->startOfDay()))
            ->when($endDate, fn($q, $date) => $q->where('created_at', '<=', Carbon::parse($date)->endOfDay()))
            ->sum('monto') ?? 0;

        // --- 3. PROYECCIONES ---
        // (Asumiendo que Payment se relaciona con User a través de Subscription)
        $future_projections = Payment::whereHas('subscription.user', $filtroPrueba)
            ->where('status', 'pending')
            ->where('payment_due_date', '>=', Carbon::now())
            ->select(
                DB::raw("DATE_FORMAT(payment_due_date, '%Y-%m') as month"),
                DB::raw("SUM(amount) as total_due")
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->take(12)
            ->get();

        $net_flow = ($total_in_transfers + $total_in_reinvestments) - ($total_out_withdrawals + $total_out_profits);

        // ... (el resto de tu 'return Inertia::render' se queda igual)
        return Inertia::render('Admin/Reports/FinancialDashboard', [
            'stats' => [
                'total_in_transfers' => (float) $total_in_transfers,
                'total_in_reinvestments' => (float) $total_in_reinvestments,
                'total_out_withdrawals' => (float) $total_out_withdrawals,
                'total_out_profits' => (float) $total_out_profits,
                'net_flow' => (float) $net_flow,
            ],
            'projections' => $future_projections,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        ]);
    }
}
