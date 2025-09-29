<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'image_url', // <-- Añade esto
        'calculation_type',
        'fixed_percentage',
        'percentages',
        'closed_profit_percentage',
        'closed_duration_days',
    ];

    /**
     * Define la relación "uno a muchos" con el modelo User.
     * Un plan puede ser asignado a muchos usuarios.
     */
    protected $casts = [
        'percentages' => 'array', // <-- Y esto
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
}
