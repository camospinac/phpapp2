<?php
namespace App\Mail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TransferSentEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $sender,    // <-- El que envía
        public User $recipient,
        public float $amount
    ) {}

    public function build()
    {
        return $this->subject('Confirmación de Envío de Saldo')
                    ->view('emails.transfer-sent');
    }
}