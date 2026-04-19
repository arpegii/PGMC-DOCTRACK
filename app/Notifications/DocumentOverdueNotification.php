<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    /**
     * Get the notification's delivery channels.
     * [COMMENTED OUT - EMAIL FUNCTION DISABLED] - Disabled 'mail' channel for LAN-only system
     */
    public function via($notifiable): array
    {
        // return ['mail', 'database'];
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     * [COMMENTED OUT - EMAIL FUNCTION DISABLED] - Email notifications disabled for LAN-only system
     */
    /*
    public function toMail($notifiable): MailMessage
    {
        $pendingDays = $this->getPendingDays();
        $overdueDays = $this->getOverdueDays();

        return (new MailMessage)
            ->subject('Overdue Document Alert - ' . $this->document->document_number)
            ->view('emails.document-overdue', [
                'document' => $this->document,
                'user' => $notifiable,
                'pendingDays' => $pendingDays,
                'overdueDays' => $overdueDays,
            ]);
    }
    */

    /**
     * Get the array representation for database storage.
     */
    public function toArray($notifiable): array
    {
        $pendingDays = $this->getPendingDays();
        $overdueDays = $this->getOverdueDays();
        $pendingLabel = $pendingDays === 1 ? 'day' : 'days';
        $overdueLabel = $overdueDays === 1 ? 'day' : 'days';

        return [
            'document_id' => $this->document->id,
            'document_number' => $this->document->document_number,
            'title' => $this->document->title,
            'type' => 'document_overdue',
            'message' => "Overdue document pending in your unit for {$pendingDays} {$pendingLabel} ({$overdueDays} {$overdueLabel} overdue).",
            'receiving_unit_id' => $this->document->receiving_unit_id,
            'receiving_unit' => $this->document->receivingUnit->name ?? 'Unknown Unit',
            'sender_unit' => $this->document->senderUnit->name ?? 'Unknown Unit',
            'pending_days' => $pendingDays,
            'overdue_days' => $overdueDays,
            'email_sent_at' => now()->toDateTimeString(),
            'url' => route('documents.view', $this->document->id),
        ];
    }

    protected function getPendingDays(): int
    {
        $referenceDate = $this->getPendingStartDate();

        // Count elapsed whole days since the document became pending for this unit.
        return max(0, (int) $referenceDate->diffInDays(now(), false));
    }

    protected function getOverdueDays(): int
    {
        $overdueStartDate = $this->getPendingStartDate()->copy()->addDays(3);
        $minutesOverdue = $overdueStartDate->diffInMinutes(now(), false);

        if ($minutesOverdue <= 0) {
            return 0;
        }

        // As soon as document passes the 3-day limit, it is 1 day overdue.
        // Then it increments to 2, 3, ... for succeeding days.
        return (int) ceil($minutesOverdue / (24 * 60));
    }

    protected function getPendingStartDate()
    {
        return $this->document->forwarded_at ?? $this->document->created_at;
    }
}
