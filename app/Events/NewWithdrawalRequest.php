<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Withdrawal; // <-- CORREGIDO: Usamos tu modelo Withdrawal

class NewWithdrawalRequest implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // Propiedades públicas que se enviarán al frontend
    public string $userName;
    public string $amount;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Withdrawal $withdrawal La solicitud de retiro
     */
    public function __construct(Withdrawal $withdrawal) // <-- CORREGIDO: Aceptamos Withdrawal
    {
        // Formateamos los datos que queremos enviar
        // Asegúrate de que tu modelo 'Withdrawal' tenga la relación 'user'
        $this->userName = $withdrawal->user->nombres; 
        $this->amount = number_format($withdrawal->amount, 0, ',', '.'); // <-- CORREGIDO: Usamos 'amount'
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin-notifications'),
        ];
    }
}