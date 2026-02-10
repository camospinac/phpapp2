<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromQuery, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        // 1. Reciclamos la lógica de filtros del Index
        $query = User::query()->with(['rank', 'subscriptions', 'referrals']);

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('nombres', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!empty($this->filters['location'])) {
            $query->where('location', 'like', "%{$this->filters['location']}%");
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID', 'Nombres', 'Apellidos', 'Email', 'Celular', 
            'Ubicación', 'Rol', 'Rango', 'Inversión Total', 'Referidos'
        ];
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->nombres,
            $user->apellidos,
            $user->email,
            $user->celular,
            $user->location,
            $user->rol,
            $user->rank?->name ?? 'Sin Rango',
            $user->subscriptions->sum('initial_investment'), // Suma de inversiones
            $user->referrals->count(), // Conteo de referidos
        ];
    }
}