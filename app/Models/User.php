<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\CustomResetPasswordNotification;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombres',
        'apellidos',
        'celular',
        'email',
        'password',
        'rol',
        'referral_code',
        'location',
        'referred_by_id',
        'identification_type',
        'identification_number',
        'is_fraud',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'nombres' => 'encrypted',
            'apellidos' => 'encrypted',
            'celular' => 'encrypted',
        ];
    }

    /**
     * Define la relación: Un usuario tiene muchas transacciones.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'id_user');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
    public function rank()
    {
        return $this->belongsTo(Rank::class);
    }

    // Para obtener la lista de usuarios que este ha referido
    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by_id');
    }
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new CustomResetPasswordNotification($token));
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }
}
