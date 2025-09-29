<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rank extends Model
{
    use HasFactory;

    // Le damos permiso para llenar estos campos masivamente
    protected $fillable = [
        'name',
        'required_referrals',
        'reward_description',
        'reward_type',
        'reward_amount',
        'is_active',
    ];

    // Un rango puede tener muchos usuarios
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
