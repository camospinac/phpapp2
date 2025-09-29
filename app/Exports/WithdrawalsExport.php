<?php

namespace App\Exports;

use App\Models\Withdrawal;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WithdrawalsExport implements FromQuery, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct(array $filters) { $this->filters = $filters; }

    public function headings(): array
    {
        return ['Código', 'Usuario', 'Monto', 'Estado', 'Fecha Solicitud', 'Fecha Pago'];
    }

    public function map($withdrawal): array
    {
        return [
            $withdrawal->code,
            $withdrawal->user->nombres . ' ' . $withdrawal->user->apellidos,
            $withdrawal->amount,
            $withdrawal->status,
            $withdrawal->created_at->format('Y-m-d H:i:s'),
            $withdrawal->status === 'completed' ? $withdrawal->updated_at->format('Y-m-d H:i:s') : 'N/A',
        ];
    }

    public function query()
    {
        return Withdrawal::query()->with('user')
            ->when($this->filters['start_date'] ?? null, fn($q, $v) => $q->whereDate('updated_at', '>=', $v))
            ->when($this->filters['end_date'] ?? null, fn($q, $v) => $q->whereDate('updated_at', '<=', $v))
            ->when($this->filters['status'] ?? null, fn($q, $v) => $q->where('status', $v));
    }
}