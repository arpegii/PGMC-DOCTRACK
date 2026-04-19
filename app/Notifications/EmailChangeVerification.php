<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailChangeVerification extends Notification
{
    use Queueable;

    public function __construct(
        public string $token,
        public string $newEmail,
        public ?string $recipientName = null
    ) {}

    public function via($notifiable): array
    {
        // [COMMENTED OUT - EMAIL FUNCTION DISABLED] - Disabled email channel for LAN-only system
        // return ['mail'];
        return [];
    }

    /**
     * [COMMENTED OUT - EMAIL FUNCTION DISABLED] - Email notifications disabled for LAN-only system
     */
    /*
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = route('email-change.verify', [
            'token' => $this->token,
            'email' => $this->newEmail,
        ]);
        $name = $this->recipientName
            ?? data_get($notifiable, 'name')
            ?? 'there';

        return (new MailMessage)
            ->subject('Verify Your New Email Address')
            ->view('emails.email-change-verification', [
                'name' => $name,
                'newEmail' => $this->newEmail,
                'verificationUrl' => $verificationUrl,
            ]);
    }
    */
}
