<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class RejectedController extends Controller
{
    /**
     * Display rejected documents
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $searchQuery = trim((string) $request->input('search', ''));
        $allowedPerPage = [10, 25, 50, 100];
        $perPage = (int) $request->integer('per_page', 10);
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }
        $selectedUnitId = null;
        $filterUnits = $user->isAdmin() ? Unit::all() : Unit::visibleToUser($user);

        if ($request->has('unit_id')) {
            $unitIdInput = $request->input('unit_id');
            if (empty($unitIdInput) || $unitIdInput === '0' || $unitIdInput === 0) {
                $selectedUnitId = null;
                $request->session()->put('unit_filter', 'all');
            } else {
                $selectedUnitId = (int) $unitIdInput;
                $request->session()->put('unit_filter', $selectedUnitId);
            }
        } else {
            $sessionFilter = $request->session()->get('unit_filter');
            if ($sessionFilter === 'all') {
                $selectedUnitId = null;
            } elseif ($sessionFilter) {
                $selectedUnitId = (int) $sessionFilter;
            } else {
                $selectedUnitId = null;
            }
        }

        $units = Unit::visibleToUser($user);
        $documentTypes = DocumentType::orderBy('name')->get();

        if ($user->isAdmin()) {
            $query = Document::with([
                'senderUnit', 'receivingUnit', 'creator', 'rejectedBy',
                'resubmitHistory.resubmittedByUser', 'forwardHistory.fromUnit', 'forwardHistory.toUnit'
            ])
                ->where('status', 'rejected');

            if ($selectedUnitId) {
                $query->where(function ($subQuery) use ($selectedUnitId) {
                    $subQuery->where('sender_unit_id', $selectedUnitId)
                        ->orWhere('receiving_unit_id', $selectedUnitId);
                });
            }

            if ($searchQuery !== '') {
                $query->where(function ($subQuery) use ($searchQuery) {
                    $subQuery->where('document_number', 'like', "%{$searchQuery}%")
                        ->orWhere('title', 'like', "%{$searchQuery}%")
                        ->orWhere('document_type', 'like', "%{$searchQuery}%")
                        ->orWhereHas('senderUnit', function ($unitQuery) use ($searchQuery) {
                            $unitQuery->where('name', 'like', "%{$searchQuery}%");
                        })
                        ->orWhereHas('receivingUnit', function ($unitQuery) use ($searchQuery) {
                            $unitQuery->where('name', 'like', "%{$searchQuery}%");
                        });
                });
            }

            $documents = $query->orderBy('created_at', 'desc')
                ->paginate($perPage)
                ->withQueryString();
        } else {
            $query = Document::with([
                'senderUnit', 'receivingUnit', 'creator', 'rejectedBy',
                'resubmitHistory.resubmittedByUser', 'forwardHistory.fromUnit', 'forwardHistory.toUnit'
            ])
                ->where('status', 'rejected')
                ->where('sender_unit_id', $user->unit_id);

            if ($selectedUnitId) {
                $query->where('receiving_unit_id', $selectedUnitId);
            }

            if ($searchQuery !== '') {
                $query->where(function ($subQuery) use ($searchQuery) {
                    $subQuery->where('document_number', 'like', "%{$searchQuery}%")
                        ->orWhere('title', 'like', "%{$searchQuery}%")
                        ->orWhere('document_type', 'like', "%{$searchQuery}%")
                        ->orWhereHas('senderUnit', function ($unitQuery) use ($searchQuery) {
                            $unitQuery->where('name', 'like', "%{$searchQuery}%");
                        })
                        ->orWhereHas('receivingUnit', function ($unitQuery) use ($searchQuery) {
                            $unitQuery->where('name', 'like', "%{$searchQuery}%");
                        });
                });
            }

            $documents = $query->orderBy('created_at', 'desc')
                ->paginate($perPage)
                ->withQueryString();
        }

        return view('rejected.rejected', compact(
            'documents',
            'units',
            'filterUnits',
            'selectedUnitId',
            'documentTypes'
        ));
    }

    /**
     * Resubmit a rejected document.
     * Snapshots the before/after state into document_resubmit_history,
     * increments the attempt counter, clears the rejection reason, and
     * sets status back to 'incoming' so it lands in the receiving unit's
     * incoming queue.
     */
    public function resubmit(Request $request, $id)
    {
        $user     = Auth::user();
        $document = Document::findOrFail($id);

        // Only the sender unit or an admin may resubmit
        if (!$user->isAdmin() && (int) $document->sender_unit_id !== (int) $user->unit_id) {
            abort(403, 'You are not authorised to resubmit this document.');
        }

        // Must still be in rejected state
        if ($document->status !== 'rejected') {
            return redirect()->route('rejected.index')
                ->with('error', 'Only rejected documents can be resubmitted.');
        }

        // Get accessible units for validation
        $accessibleUnitIds = $user->isAdmin()
            ? Unit::all()->pluck('id')->toArray()
            : Unit::visibleToUser($user)->pluck('id')->toArray();

        $request->validate([
            'title'             => 'required|string|max:255',
            'receiving_unit_id' => [
                'required',
                'exists:units,id',
                function ($attribute, $value, $fail) use ($user, $accessibleUnitIds) {
                    $value = (int) $value;

                    if (!in_array($value, $accessibleUnitIds, true)) {
                        $fail('You cannot send documents to this unit.');
                    }
                    if (!$user->isAdmin() && $value === (int) Unit::ADMIN_UNIT_ID) {
                        $fail('You cannot send documents to the admin unit.');
                    }
                    if ($value === (int) $user->unit_id) {
                        $fail('You cannot send a document to your own unit.');
                    }
                },
            ],
            'document_type'  => 'required|string|max:255',
            'resubmit_notes' => 'nullable|string|max:1000',
            'file'           => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:25600',
        ], [
            'file.max'   => 'The file must not be larger than 25MB.',
            'file.mimes' => 'The file must be a PDF, DOC, DOCX, JPG, or PNG.',
        ]);

        // ── Snapshot BEFORE values ────────────────────────────────
        $previousTitle           = $document->title;
        $previousDocumentType    = $document->document_type;
        $previousReceivingUnitId = $document->receiving_unit_id;
        $previousFilePath        = $document->file_path;
        $previousFileName        = $document->file_name;
        $previousRejectionReason = $document->rejection_reason;

        // Capture the rejection timestamp BEFORE we clear it on the document.
        // This is used to correctly sort the rejection event in the timeline,
        // placing it before the resubmission event chronologically.
        $rejectedAt = $document->rejected_at ?? now();

        // ── Apply new file if provided ────────────────────────────
        $newFilePath = $previousFilePath;
        $newFileName = $previousFileName;

        if ($request->hasFile('file')) {
            try {
                if ($previousFilePath && Storage::exists($previousFilePath)) {
                    Storage::delete($previousFilePath);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to delete previous document file during resubmit', [
                    'document_id' => $document->id,
                    'file_path'   => $previousFilePath,
                    'error'       => $e->getMessage(),
                ]);
            }

            $newFilePath = $request->file('file')->store('documents');
            $newFileName = $request->file('file')->getClientOriginalName();
        }

        // ── Update document ───────────────────────────────────────
        $nextAttempt = ($document->resubmit_count ?? 0) + 1;

        $document->title               = $request->title;
        $document->receiving_unit_id   = $request->receiving_unit_id;
        $document->document_type       = $request->document_type;
        $document->file_path           = $newFilePath;
        $document->file_name           = $newFileName;
        $document->resubmit_notes      = $request->resubmit_notes;
        $document->resubmit_count      = $nextAttempt;
        $document->last_resubmitted_at = now();
        $document->last_resubmitted_by = $user->id;
        $document->status              = 'incoming';
        $document->rejection_reason    = null;
        $document->rejected_at         = null; // clear so it doesn't show as still rejected
        $document->rejected_by         = null;

        $document->save();

        // ── Write immutable history row ───────────────────────────
        try {
            \App\Models\DocumentResubmitHistory::create([
                'document_id'                => $document->id,
                'attempt'                    => $nextAttempt,
                'previous_title'             => $previousTitle,
                'previous_document_type'     => $previousDocumentType,
                'previous_receiving_unit_id' => $previousReceivingUnitId,
                'previous_file_path'         => $previousFilePath,
                'previous_file_name'         => $previousFileName,
                'new_title'                  => $document->title,
                'new_document_type'          => $document->document_type,
                'new_receiving_unit_id'      => $document->receiving_unit_id,
                'new_file_path'              => $newFilePath,
                'new_file_name'              => $newFileName,
                'rejection_reason'           => $previousRejectionReason,
                'rejected_at'                => $rejectedAt, // ← new: exact rejection timestamp
                'resubmit_notes'             => $request->resubmit_notes,
                'resubmitted_by'             => $user->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to write resubmit history', [
                'document_id' => $document->id,
                'error'       => $e->getMessage(),
            ]);
        }

        // ── Notify receiver unit users ────────────────────────────
        // Database notifications for resubmitted documents
        try {
            $document->load(['senderUnit', 'receivingUnit', 'creator']);

            $receivingUnitUsers = User::where('unit_id', $document->receiving_unit_id)->get();

            foreach ($receivingUnitUsers as $receivingUser) {
                try {
                    $receivingUser->notify(new \App\Notifications\DocumentResubmittedNotification(
                        $document,
                        $user
                    ));
                } catch (\Throwable $e) {
                    Log::warning('Failed to send resubmit notification', [
                        'document_id' => $document->id,
                        'user_id'     => $receivingUser->id,
                        'error'       => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Resubmit notification block failed', [
                'document_id' => $document->id,
                'error'       => $e->getMessage(),
            ]);
        }

        return redirect()->route('rejected.index')
            ->with('success', 'Document resubmitted successfully and sent to the receiving unit\'s incoming queue.');
    }
}