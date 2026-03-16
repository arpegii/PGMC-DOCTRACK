<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentForwardHistory;
use App\Models\DocumentType;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\DocumentSentNotification;
use App\Notifications\DocumentReceivedNotification;
use App\Notifications\DocumentRejectedNotification;
use App\Notifications\DocumentForwardedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    /**
     * Get the next document number for auto-increment
     */
    public function getNextDocumentNumber()
    {
        /** @var User $user */
        $user = Auth::user();

        try {
            if (!$user->unit_id) {
                Log::warning('User attempting to get document number without unit assignment', [
                    'user_id' => $user->id,
                    'user_email' => $user->email
                ]);

                return response()->json([
                    'error' => 'User is not assigned to a unit',
                    'message' => 'Please contact your administrator to assign you to a unit.'
                ], 400);
            }

            /** @var Unit|null $unit */
            $unit = Unit::find($user->unit_id);

            if (!$unit) {
                return response()->json([
                    'error' => 'Unit not found',
                    'message' => 'The assigned unit no longer exists.'
                ], 400);
            }

            $year = now()->year;

            /** @var Document|null $lastDocument */
            $lastDocument = Document::where('sender_unit_id', $user->unit_id)
                ->whereYear('created_at', $year)
                ->orderBy('id', 'desc')
                ->first();

            $nextNumber = 1;

            if ($lastDocument && $lastDocument->document_number) {
                preg_match('/\d+$/', $lastDocument->document_number, $matches);
                $lastNumber = isset($matches[0]) ? (int)$matches[0] : 0;
                $nextNumber = $lastNumber + 1;
            }

            $unitCode = strtoupper(substr($unit->name, 0, 4)) ?: 'UNIT';
            $documentNumber = sprintf('%s-%s-%03d', $unitCode, $year, $nextNumber);

            return response()->json([
                'success' => true,
                'document_number' => $documentNumber
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating document number: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Failed to generate document number',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Show the form for creating a new document
     */
    public function create()
    {
        /** @var User $user */
        $user = Auth::user();

        $units = Unit::visibleToUser($user);

        return view('documents.create', compact('units'));
    }

    /**
     * Store a new document
     */
    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->unit_id) {
            return back()->withErrors(['error' => 'You are not assigned to a unit.']);
        }

        $request->validate([
            'document_number' => 'required|unique:documents,document_number',
            'title' => 'required|string|max:255',
            'receiving_unit_id' => [
                'required',
                'exists:units,id',
                function ($attribute, $value, $fail) use ($user) {
                    if (!$user->isAdmin() && $value == Unit::ADMIN_UNIT_ID) {
                        $fail('You cannot send documents to this unit.');
                    }
                    if ($value == $user->unit_id) {
                        $fail('You cannot send a document to your own unit.');
                    }
                },
            ],
            'document_type' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:25600',
        ], [
            'file.max' => 'The file must not be larger than 25MB.',
            'file.mimes' => 'The file must be a PDF, DOC, DOCX, JPG, or PNG.',
        ]);

        $filePath = null;
        $fileNameOriginal = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $extension = strtolower($file->extension() ?: $file->getClientOriginalExtension());
            $fileName = Str::uuid()->toString() . '.' . $extension;
            $filePath = $file->storeAs('documents', $fileName, 'local');
            $fileNameOriginal = $file->getClientOriginalName();
        }

        $document = Document::create([
            'document_number' => $request->document_number,
            'title' => $request->title,
            'document_type' => $request->document_type,
            'sender_unit_id' => $user->unit_id,
            'receiving_unit_id' => $request->receiving_unit_id,
            'file_path' => $filePath,
            'file_name' => $fileNameOriginal,
            'status' => 'incoming',
            'created_by' => $user->id,
        ]);

        // Load relationships before sending notifications to avoid N+1 queries
        $document->load(['senderUnit', 'receivingUnit', 'creator']);

        // Send notification to all users in the receiving unit
        $receivingUnitUsers = User::where('unit_id', $request->receiving_unit_id)
            ->whereNotNull('email')
            ->get();

        if ($receivingUnitUsers->count() > 0) {
            foreach ($receivingUnitUsers as $receivingUser) {
                $receivingUser->notify(new DocumentSentNotification($document));
            }
        }

        return redirect()->route('incoming.index')
            ->with('success', 'Document sent successfully!');
    }

    /**
     * Mark document as received
     */
    public function receive(int $id)
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var Document $document */
        $document = Document::findOrFail($id);

        if ($document->receiving_unit_id !== $user->unit_id && !$user->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        if ($document->status !== 'incoming') {
            return back()->with('error', 'Document cannot be received. Current status: ' . $document->status);
        }

        $document->update([
            'status' => 'received',
            'received_at' => now(),
            'received_by' => $user->id,
        ]);

        // Notify ONLY the original sender (creator of the document)
        if ($document->creator && $document->creator->email) {
            $document->creator->notify(new DocumentReceivedNotification($document, $user));
        }

        return back()->with('success', 'Document marked as received!');
    }

    /**
     * Mark document as rejected
     */
    public function reject(Request $request, int $id)
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        /** @var Document $document */
        $document = Document::findOrFail($id);

        if ($document->receiving_unit_id !== $user->unit_id && !$user->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        if ($document->status !== 'incoming') {
            return back()->with('error', 'Document cannot be rejected. Current status: ' . $document->status);
        }

        $rejectionReason = $request->input('rejection_reason');

        $document->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => $user->id,
            'rejection_reason' => $rejectionReason,
        ]);

        // Notify ONLY the original sender (creator of the document)
        if ($document->creator && $document->creator->email) {
            $document->creator->notify(new DocumentRejectedNotification($document, $user));
        }

        return back()->with('success', 'Document rejected successfully!');
    }

    /**
     * Forward document to another unit
     */
    public function forward(Request $request, int $id)
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'forward_to_unit_id' => 'required|exists:units,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        /** @var Document $document */
        $document = Document::findOrFail($id);

        if ($document->receiving_unit_id !== $user->unit_id && !$user->isAdmin()) {
            return redirect()->back()->with('error', 'You are not authorized to forward this document.');
        }

        if ($document->status !== 'received') {
            return back()->with('error', 'Only received documents can be forwarded.');
        }

        if ($request->forward_to_unit_id == $document->receiving_unit_id) {
            return back()->with('error', 'You cannot forward a document to your own unit.');
        }

        try {
            DB::beginTransaction();

            $forwardHistory = DocumentForwardHistory::create([
                'document_id' => $document->id,
                'from_unit_id' => $document->receiving_unit_id,
                'to_unit_id' => $request->forward_to_unit_id,
                'forwarded_by_user_id' => $user->id,
                'notes' => $request->notes,
            ]);

            $document->update([
                'receiving_unit_id' => $request->forward_to_unit_id,
                'status' => 'incoming',
                'forwarded_by' => $user->id,
                'forwarded_at' => now(),
            ]);

            // Load relationships before sending notifications
            $document->load(['senderUnit', 'receivingUnit', 'creator']);
            $forwardHistory->load(['fromUnit', 'toUnit', 'forwardedBy']);

            DB::commit();

            // Notify all users in the receiving unit (destination) after commit
            $receivingUnitUsers = User::where('unit_id', $request->forward_to_unit_id)
                ->whereNotNull('email')
                ->get();

            foreach ($receivingUnitUsers as $receivingUser) {
                try {
                    $receivingUser->notify(new DocumentForwardedNotification($document, $forwardHistory));
                } catch (\Throwable $notifyException) {
                    Log::warning('Failed to send forwarded notification', [
                        'document_id' => $document->id,
                        'receiving_user_id' => $receivingUser->id,
                        'error' => $notifyException->getMessage(),
                    ]);
                }
            }

            return redirect()->back()->with('success', 'Document forwarded successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error forwarding document: ' . $e->getMessage(), [
                'document_id' => $id,
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to forward document. Please try again.');
        }
    }

    /**
     * View document details
     */
    public function view(int $id)
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var Document $document */
        $document = Document::with([
            'senderUnit',
            'receivingUnit',
            'creator',
            'receivedBy',
            'rejectedBy',
            'forwardHistory.fromUnit',
            'forwardHistory.toUnit',
            'forwardHistory.forwardedBy',
            'lastResubmittedByUser',  // ← added for resubmit notes display
        ])->findOrFail($id);

        if (!$user->isAdmin()) {
            $hasAccess = $document->sender_unit_id === $user->unit_id
                      || $document->receiving_unit_id === $user->unit_id
                      || $document->forwardHistory->contains(function ($history) use ($user) {
                            return $history->from_unit_id === $user->unit_id 
                                || $history->to_unit_id === $user->unit_id;
                         });

            if (!$hasAccess) {
                abort(403, 'You do not have access to view this document.');
            }
        }

        return view('incoming.view', compact('document'));
    }

    /**
     * Download document file
     */
    public function download(int $id)
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var Document $document */
        $document = Document::with('forwardHistory')->findOrFail($id);

        if (!$user->isAdmin()) {
            $hasAccess = $document->sender_unit_id === $user->unit_id
                      || $document->receiving_unit_id === $user->unit_id
                      || $document->forwardHistory->contains(function ($history) use ($user) {
                            return $history->from_unit_id === $user->unit_id 
                                || $history->to_unit_id === $user->unit_id;
                         });

            if (!$hasAccess) {
                abort(403, 'You do not have access to download this document.');
            }
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        if (!$document->file_path || !$disk->exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        return $disk->download($document->file_path, $document->file_name ?: null);
    }

    /**
     * Show forwarded documents sent by the current user's unit.
     */
    public function forwarded(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $searchQuery = trim((string) $request->input('search', ''));
        $allowedPerPage = [10, 25, 50, 100];
        $perPage = (int) $request->integer('per_page', 10);
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }
        $selectedUnitId = null;
        // Make filter units available to all users
        $filterUnits = $user->isAdmin() ? Unit::all() : Unit::visibleToUser($user);

        // Handle unit filtering for both admins and regular users
        if ($request->has('unit_id')) {
            $selectedUnitId = $request->input('unit_id');
            if ($selectedUnitId) {
                $request->session()->put('unit_filter_id', $selectedUnitId);
            } else {
                $request->session()->forget('unit_filter_id');
            }
        } else {
            $selectedUnitId = $request->session()->get('unit_filter_id');
        }

        $forwardHistoriesQuery = DocumentForwardHistory::with([
            'document.senderUnit',
            'document.receivingUnit',
            'document.creator',
            'fromUnit',
            'toUnit',
            'forwardedBy'
        ])->whereHas('document', function ($query) use ($user) {
            if (!$user->isAdmin()) {
                $query->where('sender_unit_id', $user->unit_id);
            }

            // "Forwarded" is represented by having forwarding activity.
            $query->whereNotNull('forwarded_at');
        });

        if ($selectedUnitId) {
            if ($user->isAdmin()) {
                // For admins, filter by unit in document or forwarding history
                $forwardHistoriesQuery->where(function ($query) use ($selectedUnitId) {
                    $query->whereHas('document', function ($docQuery) use ($selectedUnitId) {
                        $docQuery->where('sender_unit_id', $selectedUnitId)
                            ->orWhere('receiving_unit_id', $selectedUnitId);
                    })->orWhere('from_unit_id', $selectedUnitId)
                      ->orWhere('to_unit_id', $selectedUnitId);
                });
            } else {
                // For non-admins, filter by unit in forwarding history (from/to units)
                $forwardHistoriesQuery->where(function ($query) use ($selectedUnitId) {
                    $query->where('from_unit_id', $selectedUnitId)
                          ->orWhere('to_unit_id', $selectedUnitId);
                });
            }
        }

        if ($searchQuery !== '') {
            $forwardHistoriesQuery->where(function ($query) use ($searchQuery) {
                $query->whereHas('document', function ($docQuery) use ($searchQuery) {
                    $docQuery->where('document_number', 'like', "%{$searchQuery}%")
                        ->orWhere('title', 'like', "%{$searchQuery}%")
                        ->orWhere('document_type', 'like', "%{$searchQuery}%")
                        ->orWhereHas('senderUnit', function ($unitQuery) use ($searchQuery) {
                            $unitQuery->where('name', 'like', "%{$searchQuery}%");
                        })
                        ->orWhereHas('receivingUnit', function ($unitQuery) use ($searchQuery) {
                            $unitQuery->where('name', 'like', "%{$searchQuery}%");
                        });
                })->orWhereHas('fromUnit', function ($unitQuery) use ($searchQuery) {
                    $unitQuery->where('name', 'like', "%{$searchQuery}%");
                })->orWhereHas('toUnit', function ($unitQuery) use ($searchQuery) {
                    $unitQuery->where('name', 'like', "%{$searchQuery}%");
                });
            });
        }

        $forwardHistories = $forwardHistoriesQuery
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $documentTypes = DocumentType::orderBy('name')->get();
        $units = Unit::all();

        return view('forwarded.forwarded', compact(
            'forwardHistories',
            'units',
            'filterUnits',
            'selectedUnitId',
            'documentTypes'
        ));
    }
}
