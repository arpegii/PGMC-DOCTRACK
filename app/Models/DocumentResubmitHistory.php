<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentResubmitHistory extends Model
{
    protected $table = 'document_resubmit_history';

    protected $fillable = [
        'document_id',
        'attempt',
        'previous_title',
        'previous_document_type',
        'previous_receiving_unit_id',
        'previous_file_path',
        'previous_file_name',
        'new_title',
        'new_document_type',
        'new_receiving_unit_id',
        'new_file_path',
        'new_file_name',
        'rejection_reason',
        'resubmit_notes',
        'resubmitted_by',
        'rejected_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function resubmittedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resubmitted_by');
    }

    public function previousReceivingUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'previous_receiving_unit_id');
    }

    public function newReceivingUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'new_receiving_unit_id');
    }

    // ── Helpers ──────────────────────────────────────────────────

    /**
     * Returns an array of human-readable change descriptions.
     * e.g. ["Title changed from "Old" to "New"", "Type changed …"]
     */
    public function changesDescription(): array
    {
        $changes = [];

        if ($this->previous_title !== $this->new_title) {
            $changes[] = "Title changed from \"{$this->previous_title}\" to \"{$this->new_title}\"";
        }
        if ($this->previous_document_type !== $this->new_document_type) {
            $changes[] = "Type changed from \"{$this->previous_document_type}\" to \"{$this->new_document_type}\"";
        }
        if ((string) $this->previous_receiving_unit_id !== (string) $this->new_receiving_unit_id) {
            $prev = $this->previousReceivingUnit->name ?? $this->previous_receiving_unit_id;
            $next = $this->newReceivingUnit->name    ?? $this->new_receiving_unit_id;
            $changes[] = "Receiving unit changed from \"{$prev}\" to \"{$next}\"";
        }
        if ($this->previous_file_path !== $this->new_file_path) {
            $changes[] = 'File was replaced';
        }

        return $changes ?: ['No field changes — resubmitted as-is'];
    }
}
