<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $document;
    protected $rejectedBy;

    public function __construct(Document $document, $rejectedBy)
    {
        $this->document = $document;
        $this->rejectedBy = $rejectedBy;
    }

    /**
     * Get the notification's delivery channels.
     * [COMMENTED OUT - EMAIL FUNCTION DISABLED] - Disabled 'mail' channel for LAN-only system
     */
    public function via($notifiable)
    {
        // return ['mail', 'database'];
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     * [COMMENTED OUT - EMAIL FUNCTION DISABLED] - Email notifications disabled for LAN-only system
     */
    /*
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Document Rejected - ' . $this->document->document_number)
            ->view('emails.document-rejected', [
                'document' => $this->document,
                'user' => $notifiable,
                'rejectedBy' => $this->rejectedBy
            ]);
    }
    */

    /**
     * Get the array representation for database storage.
     */
    public function toArray($notifiable)
    {
        return [
            'document_id' => $this->document->id,
            'document_number' => $this->document->document_number,
            'title' => $this->document->title,
            'type' => 'document_rejected',
            'message' => 'Your document was rejected by ' . $this->rejectedBy->name,
            'rejected_by' => $this->rejectedBy->name,
            'rejection_reason' => $this->document->rejection_reason ?: 'No reason provided',
            'rejected_at' => $this->document->rejected_at->format('F j, Y g:i A'),
            'url' => route('documents.view', $this->document->id),
        ];
    }
}