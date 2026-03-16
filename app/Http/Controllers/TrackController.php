<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackController extends Controller
{
    /**
     * Display track/search page
     * UPDATED: Now includes forwarding history and resubmit history in access control
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $allowedPerPage = [10, 25, 50, 100];
        $perPage = (int) $request->integer('per_page', 10);
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }

        $documents = collect(); // Empty collection initially
        $searchQuery = trim((string) $request->input('search', ''));
        
        if ($searchQuery !== '') {
            // Build base query with access control and eager load all relationships
            $query = Document::with([
                'senderUnit',
                'receivingUnit',
                'creator',
                'receivedBy',
                'rejectedBy',
                'forwardHistory.fromUnit',
                'forwardHistory.toUnit',
                'forwardHistory.forwardedBy',
                'resubmitHistory.resubmittedByUser',       // ← added
                'resubmitHistory.previousReceivingUnit',   // ← added
                'resubmitHistory.newReceivingUnit',        // ← added
            ]);
            
            // Apply access control - includes forwarding history
            if (!$user->isAdmin()) {
                $query->where(function($q) use ($user) {
                    $q->where('sender_unit_id', $user->unit_id)
                      ->orWhere('receiving_unit_id', $user->unit_id)
                      ->orWhereHas('forwardHistory', function($subQuery) use ($user) {
                          $subQuery->where('from_unit_id', $user->unit_id)
                                   ->orWhere('to_unit_id', $user->unit_id);
                      });
                });
            }
            
            // Strict search: require exact full document number match.
            $query->whereRaw('LOWER(document_number) = ?', [mb_strtolower($searchQuery)]);
            
            $documents = $query->orderBy('created_at', 'desc')
                ->paginate($perPage)
                ->withQueryString();
        }
        
        return view('track.track', compact('documents', 'searchQuery'));
    }
}