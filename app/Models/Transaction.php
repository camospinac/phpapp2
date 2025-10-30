<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Transaction extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id_user',
        'subscription_id',
        'tipo',
        'monto',
        'observacion',
    ];

    protected function casts(): array
    {
        return [
            'observacion' => 'encrypted',
            'monto' => 'decimal:2',
        ];
    }

    /**
     * Define la relación inversa: Una transacción pertenece a un usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }
    public function subscription(): BelongsTo
{
    return $this->belongsTo(Subscription::class); // Asume 'subscription_id'
}

    protected $appends = ['type_detail'];
    protected function typeDetail(): Attribute
    {
        return Attribute::make(
            get: function () {
                $observation = strtolower($this->observacion ?? '');
                $type = $this->tipo;

                if ($type === 'retiro') {
                    if (str_contains($observation, 'envío de saldo')) {
                        return 'Envío entre Cuentas';
                    } elseif (str_contains($observation, 'solicitud de retiro')) {
                        return 'Solicitud de Saldo';
                    }
                    return 'Reinvertido en Plan';
                }

                if ($type === 'abono') {
                    if (str_contains($observation, 'recompensa')) {
                        return 'Recompensa de Rango';
                    } elseif (str_contains($observation, 'recepción de saldo')) {
                        return 'Recepción entre Cuentas';
                    }
                    return 'Abono de Cuota';
                }

                return 'Otro';
            },
        );
    }
}
