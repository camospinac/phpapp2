<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'plan_id',
        'amount',
        'percentage',
        'status',
        'payment_due_date',
    ];

    /**
     * Define la relación "pertenece a" con el modelo User.
     * Cada pago pertenece a un único usuario.
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
