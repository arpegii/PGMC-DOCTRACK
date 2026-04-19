<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPassword
{
    /**
     * [COMMENTED OUT - EMAIL FUNCTION DISABLED] - Email notifications disabled for LAN-only system
     */
    /*
    public function toMail($notifiable): MailMessage
    {
        $resetUrl = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        $passwordBroker = config('auth.defaults.passwords');
        $expireMinutes = config("auth.passwords.{$passwordBroker}.expire", 60);

        return (new MailMessage)
            ->subject('Reset Your Password')
            ->view('emails.password-reset', [
                'name' => $notifiable->name ?? 'there',
                'resetUrl' => $resetUrl,
                'expireMinutes' => $expireMinutes,
            ]);
    }
    */
}
