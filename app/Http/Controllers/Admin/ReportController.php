<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Withdrawal;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SubscriptionsExport;
use App\Exports\PaymentsExport;
use App\Exports\WithdrawalsExport;

class ReportController extends Controller
{
    public function subscriptions(Request $request)
    {
        $subscriptions = Subscription::with(['user', 'plan'])
            ->when($request->input('start_date'), function ($query, $startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($request->input('end_date'), function ($query, $endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            })
            ->when($request->input('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/Reports/Subscriptions', [
            'subscriptions' => $subscriptions,
            'filters' => $request->only(['start_date', 'end_date', 'status']),
        ]);
    }

    public function payments(Request $request)
    {
        $payments = Payment::with(['subscription.user', 'subscription.plan'])
            ->when($request->input('start_date'), function ($query, $startDate) {
                $query->whereDate('payment_due_date', '>=', $startDate);
            })
            ->when($request->input('end_date'), function ($query, $endDate) {
                $query->whereDate('payment_due_date', '<=', $endDate);
            })
            ->when($request->input('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest('payment_due_date')
            ->paginate(15);

        return Inertia::render('Admin/Reports/Payments', [
            'payments' => $payments,
            'filters' => $request->only(['start_date', 'end_date', 'status']),
        ]);
    }

    public function withdrawals(Request $request)
    {
        $withdrawals = Withdrawal::with('user')
            ->when($request->input('start_date'), function ($query, $startDate) {
                // Filtramos por 'updated_at' porque nos interesa saber cuándo se completó
                $query->whereDate('updated_at', '>=', $startDate);
            })
            ->when($request->input('end_date'), function ($query, $endDate) {
                $query->whereDate('updated_at', '<=', $endDate);
            })
            ->when($request->input('status'), function ($query, $status) {
                $query->where('status', 'like', "%{$status}%");
            })
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/Reports/Withdrawals', [
            'withdrawals' => $withdrawals,
            'filters' => $request->only(['start_date', 'end_date', 'status']),
        ]);
    }

    public function exportSubscriptions(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'status']);

        return Excel::download(new SubscriptionsExport($filters), 'suscripciones.xlsx');
    }

    public function exportPayments(Request $request)
    {
        return Excel::download(new PaymentsExport($request->all()), 'pagos.xlsx');
    }

    public function exportWithdrawals(Request $request)
    {
        return Excel::download(new WithdrawalsExport($request->all()), 'retiros.xlsx');
    }
}
