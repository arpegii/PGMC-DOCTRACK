<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Unit;
use App\Models\User;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_number',
        'title',
        'document_type',
        'file_path',
        'file_name',
        'sender_unit_id',
        'receiving_unit_id',
        'status',
        'received_at',
        'received_by',
        'rejected_at',
        'rejection_reason',
        'rejected_by',
        'forwarded_by',
        'forwarded_at',
        'forwarding_notes',
        'original_document_id',
        'created_by',
        // Resubmission fields
        'resubmit_notes',
        'resubmit_count',
        'last_resubmitted_at',
        'last_resubmitted_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
        'rejected_at'         => 'datetime',
        'received_at'         => 'datetime',
        'forwarded_at'        => 'datetime',
        'last_resubmitted_at' => 'datetime',
        'resubmit_count'      => 'integer',
    ];

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo */
    public function senderUnit()
    {
        return $this->belongsTo(Unit::class, 'sender_unit_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo */
    public function receivingUnit()
    {
        return $this->belongsTo(Unit::class, 'receiving_unit_id');
    }

    /** @param int $unitId */
    public function isIncomingFor(int $unitId): bool
    {
        return $this->receiving_unit_id == $unitId && $this->status === 'incoming';
    }

    /** @param int $unitId */
    public function isOutgoingFrom(int $unitId): bool
    {
        return $this->sender_unit_id == $unitId;
    }

    /** @param User $user */
    public function userHasAccess(User $user): bool
    {
        return $user->isAdmin() ||
               $this->sender_unit_id === $user->unit_id ||
               $this->receiving_unit_id === $user->unit_id;
    }

    /**
     * Get the forwarding history for this document
     */
    public function forwardHistory()
    {
        return $this->hasMany(DocumentForwardHistory::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get the user who created this document
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who received this document
     */
    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Get the user who rejected this document
     */
    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * All resubmission history entries for this document,
     * oldest first (attempt 1, 2, 3 …)
     */
    public function resubmitHistory()
    {
        return $this->hasMany(DocumentResubmitHistory::class, 'document_id')
                    ->orderBy('attempt', 'asc');
    }

    /**
     * The user who last resubmitted this document
     */
    public function lastResubmittedByUser()
    {
        return $this->belongsTo(User::class, 'last_resubmitted_by');
    }
}
