<?php
namespace App\Notifications;

use App\Mail\CustomResetPasswordEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class CustomResetPasswordNotification extends Notification
{
    use Queueable;
    public string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): CustomResetPasswordEmail
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new CustomResetPasswordEmail($resetUrl, $notifiable->nombres))
            ->to($notifiable->getEmailForPasswordReset());
    }
}