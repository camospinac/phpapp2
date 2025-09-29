<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PaymentsExport implements FromQuery, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct(array $filters) { $this->filters = $filters; }

    public function headings(): array
    {
        return ['# Pago', 'Usuario', 'Monto', 'Estado', 'Fecha de Vencimiento'];
    }

    public function map($payment): array
    {
        return [
            $payment->id,
            $payment->subscription->user->nombres . ' ' . $payment->subscription->user->apellidos,
            $payment->amount,
            $payment->status,
            $payment->payment_due_date,
        ];
    }

    public function query()
    {
        return Payment::query()->with(['subscription.user'])
            ->when($this->filters['start_date'] ?? null, fn($q, $v) => $q->whereDate('payment_due_date', '>=', $v))
            ->when($this->filters['end_date'] ?? null, fn($q, $v) => $q->whereDate('payment_due_date', '<=', $v))
            ->when($this->filters['status'] ?? null, fn($q, $v) => $q->where('status', $v));
    }
}