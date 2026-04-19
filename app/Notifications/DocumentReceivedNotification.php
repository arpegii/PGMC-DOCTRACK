<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $document;
    protected $receivedBy;

    public function __construct(Document $document, $receivedBy)
    {
        $this->document = $document;
        $this->receivedBy = $receivedBy;
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
            ->subject('Document Received by Receiver Unit - ' . $this->document->document_number)
            ->view('emails.document-received', [
                'document' => $this->document,
                'user' => $notifiable,
                'receivedBy' => $this->receivedBy
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
            'type' => 'document_received',
            'message' => 'Your document was received by ' . $this->receivedBy->name,
            'received_by' => $this->receivedBy->name,
            'received_at' => $this->document->received_at->format('F j, Y g:i A'),
            'url' => route('documents.view', $this->document->id),
        ];
    }
}