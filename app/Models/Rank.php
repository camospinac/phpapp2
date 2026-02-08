<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rank extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar en masa.
     */
    protected $fillable = [
        'name',
        'required_referrals',
        'reward_description',
        'reward_amount',
        'is_active',
    ];

    /**
     * Los atributos que deben ser casteados.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'required_referrals' => 'integer',
        'reward_amount' => 'decimal:2',
    ];
}