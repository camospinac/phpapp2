<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Winner extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'win_date',
        'prize',
        'city',
        'photo_path',
    ];

    /**
     * Get the user who won the prize.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}