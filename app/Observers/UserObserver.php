<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Rank;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log; // Optional: for logging/debugging

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // 1. Si el nuevo usuario no fue referido, no hacemos nada.
        if (! $user->referred_by_id) {
            return;
        }

        // 2. Buscamos al "padrino" (el referidor)
        $referrer = User::find($user->referred_by_id);

        if ($referrer) {
            // 3. Obtenemos el rango actual del padrino (antes de subir el contador)
            $previousRank = $referrer->rank;
            $previousRankReferrals = $previousRank->required_referrals ?? 0;

            // 4. Incrementamos su contador de referidos
            $referrer->increment('referral_count');
            $newCount = $referrer->referral_count;
            
            // 5. Buscamos el nuevo rango que acaba de alcanzar
            $newRank = Rank::where('required_referrals', $newCount)
                           ->where('is_active', true)
                           ->first();

            // 6. ¡Premio! Si se encontró un nuevo rango...
            if ($newRank) {
                // 7. Calculamos el "tramo" de referidos para esta recompensa
                $referralsForReward = $referrer->referrals() // 'referrals' es la relación que definimos
                    ->with(['subscriptions' => fn($q) => $q->orderBy('created_at', 'asc')->limit(1)])
                    ->skip($previousRankReferrals)
                    ->take($newRank->required_referrals - $previousRankReferrals)
                    ->get();
                
                // 8. Sumamos la inversión de ese tramo de referidos
                $investmentSum = $referralsForReward->sum(function ($ref) {
                    return $ref->subscriptions->first()->initial_investment ?? 0;
                });

                // 9. Calculamos el monto de la recompensa
                $rewardAmount = $investmentSum * ($newRank->reward_percentage / 100);

                // 10. Actualizamos el rango del padrino y creamos la transacción de abono
                if ($rewardAmount > 0) {
                    $referrer->rank_id = $newRank->id;
                    $referrer->save();

                    Transaction::create([
                        'id_user' => $referrer->id,
                        'tipo' => 'abono',
                        'monto' => $rewardAmount,
                        'observacion' => "Recompensa por alcanzar el rango: {$newRank->name}",
                    ]);
                }
            }
        }
    }
}