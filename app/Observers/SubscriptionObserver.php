<?php

namespace App\Observers;

use App\Models\Subscription;
use App\Models\User;
use App\Models\Rank;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class SubscriptionObserver
{
    public function updated(Subscription $subscription): void
    {
        // 1. DISPARADOR: Solo si el status cambió a 'active'
        if ($subscription->wasChanged('status') && $subscription->status === 'active') {
            
            $user = $subscription->user;

            // 2. FILTRO ANTI-FRAUDE: Solo premiar por la PRIMERA suscripción del referido
            // Así evitamos pagar bonos cada vez que el usuario reinvierta o renueve.
            $activeSubscriptionsCount = $user->subscriptions()->where('status', 'active')->count();
            
            if ($activeSubscriptionsCount === 1 && $user->referred_by_id) {
                
                $referrer = User::find($user->referred_by_id);

                if ($referrer) {
                    DB::transaction(function () use ($referrer, $subscription) {
                        // 3. Incrementamos el contador de referidos del padrino
                        $referrer->increment('referral_count');
                        $newCount = $referrer->referral_count;

                        // 4. Buscamos si este nuevo número de referidos desbloquea un rango
                        $newRank = Rank::where('required_referrals', $newCount)
                                       ->where('is_active', true)
                                       ->first();

                        if ($newRank) {
                            // 5. Actualizamos el rango del padrino
                            $referrer->update(['rank_id' => $newRank->id]);

                            // 6. ¡PREMIO FIJO! Usamos reward_amount de la tabla ranks
                            Transaction::create([
                                'id_user'     => $referrer->id,
                                'tipo'        => 'abono',
                                'monto'       => $newRank->reward_amount,
                                'observacion' => "Bono por alcanzar el rango {$newRank->name} (Referido: {$subscription->user->nombres})",
                            ]);
                        }
                    });
                }
            }
        }
    }
}