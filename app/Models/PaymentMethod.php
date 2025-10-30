<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    // Campos que permitiremos actualizar masivamente
    protected $fillable = [
        'account_details',
        'is_active',
    ];

    // Aseguramos que 'is_active' se maneje como un booleano
    protected $casts = [
        'is_active' => 'boolean',
    ];
}