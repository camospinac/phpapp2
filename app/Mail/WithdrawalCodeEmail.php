<?php
namespace App\Mail;
use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WithdrawalCodeEmail extends Mailable
{
    use Queueable, SerializesModels;
    public Withdrawal $withdrawal;
    public function __construct(Withdrawal $withdrawal)
    {
        $this->withdrawal = $withdrawal;
    }
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Tu Código de Retiro ha sido Generado');
    }
    public function content(): Content
    {
        return new Content(view: 'emails.withdrawal-code');
    }
}