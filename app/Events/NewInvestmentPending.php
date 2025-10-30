<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // <-- Usamos 'Now'
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Subscription; // <-- El modelo de Inversión/Suscripción

class NewInvestmentPending implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $userName;
    public string $amount;

    /**
     * Create a new event instance.
     */
    public function __construct(Subscription $subscription)
    {
        // Asumimos que el modelo Subscription tiene la relación 'user'
        // y que el monto está en 'initial_investment'
        $this->userName = $subscription->user->nombres; 
        $this->amount = number_format($subscription->initial_investment, 0, ',', '.');
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        // ¡Reutilizamos el mismo canal! No hay que crear nada nuevo.
        return [
            new PrivateChannel('admin-notifications'),
        ];
    }
}