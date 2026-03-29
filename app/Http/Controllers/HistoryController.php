<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    /**
     * Display all document history (all statuses)
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
        // Make filter units available to all users
        $filterUnits = $user->isAdmin() ? Unit::all() : Unit::visibleToUser($user);

        // Handle unit filtering - persist selection across pages
        if ($request->has('unit_id')) {
            $unitIdInput = $request->input('unit_id');
            // If empty/null/0, user selected "all units"
            if (empty($unitIdInput) || $unitIdInput === '0' || $unitIdInput === 0) {
                $selectedUnitId = null;
                $request->session()->put('unit_filter', 'all');
            } else {
                // Specific unit selected
                $selectedUnitId = (int) $unitIdInput;
                $request->session()->put('unit_filter', $selectedUnitId);
            }
        } else {
            // No filter in request, check session
            $sessionFilter = $request->session()->get('unit_filter');
            if ($sessionFilter === 'all') {
                $selectedUnitId = null;
            } elseif ($sessionFilter) {
                $selectedUnitId = (int) $sessionFilter;
            } else {
                $selectedUnitId = null;
            }
        }

        // Get all units for dropdowns or displaying names
        $units = Unit::visibleToUser($user);

        // Get all document types from the database
        $documentTypes = DocumentType::orderBy('name')->get();

        if ($user->isAdmin()) {
            // Admin sees all documents
            $query = Document::with([
                'senderUnit', 'receivingUnit', 'creator', 'receivedBy', 'rejectedBy',
                'forwardHistory.fromUnit', 'forwardHistory.toUnit', 'forwardHistory.forwardedBy',
                'resubmitHistory.resubmittedByUser'
            ]);

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
            // Users see only documents where their unit is sender OR receiver
            $query = Document::with([
                'senderUnit', 'receivingUnit', 'creator', 'receivedBy', 'rejectedBy',
                'forwardHistory.fromUnit', 'forwardHistory.toUnit', 'forwardHistory.forwardedBy',
                'resubmitHistory.resubmittedByUser'
            ])
                ->where(function ($query) use ($user) {
                    $query->where('sender_unit_id', $user->unit_id)
                          ->orWhere('receiving_unit_id', $user->unit_id);
                });

            // Apply unit filter for regular users (by sender or receiver)
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
        }

        // Pass both documents and units to the view
        return view('history.history', compact(
            'documents',
            'units',
            'filterUnits',
            'selectedUnitId',
            'documentTypes'  // <-- added
        ));
    }
}