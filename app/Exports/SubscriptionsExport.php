<?php

namespace App\Exports;

use App\Models\Subscription;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SubscriptionsExport implements FromQuery, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function headings(): array
    {
        return [
            'ID Suscripción',
            'Usuario',
            'Plan',
            'Tipo Contrato',
            'Monto Invertido',
            'Estado',
            'Fecha Creación',
        ];
    }

    public function map($subscription): array
    {
        return [
            $subscription->id,
            $subscription->user->nombres . ' ' . $subscription->user->apellidos,
            $subscription->plan->name,
            $subscription->contract_type,
            $subscription->initial_investment,
            $subscription->status,
            $subscription->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function query()
    {
        // Reutilizamos la misma lógica de filtrado del controlador
        return Subscription::query()->with(['user', 'plan'])
            ->when($this->filters['start_date'] ?? null, function ($query, $startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($this->filters['end_date'] ?? null, function ($query, $endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            })
            ->when($this->filters['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest();
    }
}