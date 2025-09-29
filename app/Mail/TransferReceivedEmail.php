<?php
namespace App\Mail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TransferReceivedEmail extends Mailable
{
    use Queueable, SerializesModels;
    public User $sender;
    public User $recipient;
    public float $amount;
    
    public function __construct(User $sender, User $recipient, float $amount)
    {
        // 2. Asignamos los valores manualmente
        $this->sender = $sender;
        $this->recipient = $recipient;
        $this->amount = $amount;
    }

    public function build()
    {
        return $this->subject('¡Has Recibido Saldo!')
                    ->view('emails.transfer-received');
    }
}