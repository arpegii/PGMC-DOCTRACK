<?php

namespace App\Notifications;

use App\Models\Document;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentResubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Document $document;
    public User $resubmittedBy;

    public function __construct(Document $document, User $resubmittedBy)
    {
        $this->document      = $document;
        $this->resubmittedBy = $resubmittedBy;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Database notification — keys must match what index.blade.php and
     * the navbar partial read from $notification->data[].
     */
    public function toDatabase(object $notifiable): array
    {
        $attempt = $this->document->resubmit_count ?? 1;
        $suffix  = match ($attempt) {
            1       => '1st',
            2       => '2nd',
            3       => '3rd',
            default => $attempt . 'th',
        };

        return [
            // ── Type key — drives the icon/color switch in both blade templates
            'type'             => 'document_resubmitted',

            // ── Document info — read by the notification list cards
            'document_id'      => $this->document->id,
            'document_number'  => $this->document->document_number,
            'title'            => $this->document->title,
            'document_type'    => $this->document->document_type,

            // ── Who / where — shown in the detail lines
            'sender_unit'      => $this->document->senderUnit->name  ?? 'Unknown',
            'resubmitted_by'   => $this->resubmittedBy->name,
            'resubmit_count'   => $this->document->resubmit_count,
            'resubmit_attempt' => $suffix,
            'resubmit_notes'   => $this->document->resubmit_notes,
            'resubmitted_at'   => now()->format('M d, Y h:i A'),

            // ── Main message line shown in the bell dropdown and list header
            'message'          => 'Document #' . $this->document->document_number
                                  . ' has been resubmitted (' . $suffix . ' attempt) and is awaiting your review.',
        ];
    }

public function toMail(object $notifiable): MailMessage
{
    $attempt = $this->document->resubmit_count ?? 1;
    $suffix  = match ($attempt) {
        1       => '1st',
        2       => '2nd',
        3       => '3rd',
        default => $attempt . 'th',
    };

    return (new MailMessage)
        ->subject('Document Resubmitted: ' . $this->document->document_number)
        ->view('emails.document-resubmitted', [
            'notifiable'    => $notifiable,
            'document'      => $this->document,
            'resubmittedBy' => $this->resubmittedBy,
            'suffix'        => $suffix,
        ]);
}
}   