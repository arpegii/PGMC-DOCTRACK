<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceivedController extends Controller
{
    /**
     * Display documents marked as received from incoming.
     * For regular users this is limited to documents they personally marked as received.
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

        // Get filtered units for CREATE document form (excludes ADMN for non-admins)
        $units = Unit::visibleToUser($user);

        // Get ALL units for FORWARD dropdown (includes ADMN for forwarding)
        $allUnits = Unit::all();

        // Get all document types from the database
        $documentTypes = DocumentType::orderBy('name')->get();

        if ($user->isAdmin()) {
            // Admin sees all received documents.
            $query = Document::with(['senderUnit', 'receivingUnit', 'creator', 'receivedBy', 'forwardHistory.fromUnit', 'forwardHistory.toUnit'])
                ->where('status', 'received');

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

            $documents = $query->orderBy('updated_at', 'desc')
                ->paginate($perPage)
                ->withQueryString();
        } else {
            // Users see all received documents for their unit.
            $query = Document::with(['senderUnit', 'receivingUnit', 'creator', 'receivedBy', 'forwardHistory.fromUnit', 'forwardHistory.toUnit'])
                ->where('receiving_unit_id', $user->unit_id)
                ->where('status', 'received');

            // Apply unit filter for regular users (by sender unit)
            if ($selectedUnitId) {
                $query->where('sender_unit_id', $selectedUnitId);
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

            $documents = $query->orderBy('updated_at', 'desc')
                ->paginate($perPage)
                ->withQueryString();
        }

        // Pass documents, units (filtered), and allUnits (unfiltered) to the view
        return view('received.received', compact(
            'documents',
            'units',
            'allUnits',
            'filterUnits',
            'selectedUnitId',
            'documentTypes'  // <-- added
        ));
    }
}