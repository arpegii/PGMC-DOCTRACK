@extends('layouts.app')

@section('header')
<div class="page-hero">
    <div>
        <h1 class="page-title">Track Document</h1>
        <p class="page-subtitle">Search and track documents using the full document number</p>
    </div>
</div>
@endsection

@section('content')

<style>
    /* Alternating Timeline Styles */
    .timeline-wrapper {
        position: relative;
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Vertical Center Line */
    .timeline-wrapper::before {
        content: '';
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        width: 3px;
        height: 100%;
        background: linear-gradient(180deg, #cbd5e1 0%, #e2e8f0 100%);
        z-index: 1;
    }

    .timeline-event {
        position: relative;
        margin-bottom: 3rem;
    }

    /* Center Dot */
    .timeline-dot {
        position: absolute;
        left: 50%;
        top: 0;
        transform: translateX(-50%);
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 4px solid white;
        z-index: 10;
        box-shadow: 0 0 0 3px currentColor;
    }

    .timeline-dot.blue {
        background: #3b82f6;
        color: #3b82f6;
    }

    .timeline-dot.indigo {
        background: #6366f1;
        color: #6366f1;
    }

    .timeline-dot.purple {
        background: #8b5cf6;
        color: #8b5cf6;
    }

    .timeline-dot.green {
        background: #10b981;
        color: #10b981;
    }

    .timeline-dot.red {
        background: #ef4444;
        color: #ef4444;
    }

    .timeline-dot.yellow {
        background: #f59e0b;
        color: #f59e0b;
    }

    /* Timeline Card */
    .timeline-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
        position: relative;
    }

    .timeline-card:hover {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        transform: translateY(-2px);
    }

    /* Left-aligned card */
    .timeline-event.left {
        padding-right: 50%;
        padding-left: 0;
        text-align: right;
    }

    .timeline-event.left .timeline-card {
        margin-right: 2.5rem;
    }

    /* Right-aligned card */
    .timeline-event.right {
        padding-left: 50%;
        padding-right: 0;
        text-align: left;
    }

    .timeline-event.right .timeline-card {
        margin-left: 2.5rem;
    }

    /* Card Header */
    .card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }

    .timeline-event.left .card-header {
        flex-direction: row-reverse;
    }

    .card-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .card-title {
        font-size: 20px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .card-date {
        font-size: 13px;
        color: #64748b;
        font-weight: 500;
        margin-top: 4px;
    }

    .card-details {
        font-size: 14px;
        color: #475569;
        line-height: 1.6;
    }

    .card-details strong {
        color: #0f172a;
        font-weight: 600;
    }

    /* Border Colors */
    .border-blue { border: 2px solid #bfdbfe; }
    .border-indigo { border: 2px solid #c7d2fe; }
    .border-purple { border: 2px solid #ddd6fe; }
    .border-green { border: 2px solid #bbf7d0; }
    .border-red { border: 2px solid #fecaca; }
    .border-yellow { border: 2px solid #fde68a; }

    /* Icon Backgrounds */
    .bg-blue-light { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); }
    .bg-indigo-light { background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); }
    .bg-purple-light { background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%); }
    .bg-green-light { background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); }
    .bg-red-light { background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); }
    .bg-yellow-light { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); }

    /* Responsive */
    @media (max-width: 768px) {
        .timeline-wrapper::before {
            left: 20px;
        }

        .timeline-event.left,
        .timeline-event.right {
            padding-left: 60px;
            padding-right: 0;
            text-align: left;
        }

        .timeline-event.left .timeline-card,
        .timeline-event.right .timeline-card {
            margin-left: 0;
            margin-right: 0;
        }

        .timeline-event.left .card-header {
            flex-direction: row;
        }

        .timeline-dot {
            left: 20px;
            transform: translateX(-50%);
        }
    }
</style>

<!-- Search Section -->
<div class="flex justify-center w-full py-6">
    <div class="w-full">
        
        <!-- Search Box -->
        <div class="panel-surface p-6 mb-6">
            <form method="GET" action="{{ route('track.index') }}" class="flex gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-slate-600 mb-2">Enter document number</label>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ $searchQuery ?? '' }}"
                        placeholder="e.g. BGCU-2026-005"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3
                               focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
                               outline-none text-sm transition duration-200
                               hover:border-slate-400"
                        autofocus
                    >
                </div>
                <button 
                    type="submit"
                    class="btn-primary-modern px-8 py-3
                           flex items-center gap-2 self-end"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Search
                </button>
            </form>
        </div>

        <!-- Results Section -->
        @if(isset($searchQuery) && $searchQuery)
            @if($documents->count() > 0)
                @foreach($documents as $document)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
                    
                    <!-- Document Header -->
                    <div class="px-6 py-5 border-b bg-gradient-to-r from-blue-50 to-white">
                        <div class="flex items-start justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900 mb-1">{{ $document->document_number }}</h2>
                                <p class="text-base text-gray-700 mb-4">{{ $document->title }}</p>
                                <div class="grid grid-cols-3 gap-6">
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">Type</p>
                                        <p class="text-sm font-medium text-gray-900">{{ $document->document_type }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">From</p>
                                        <p class="text-sm font-medium text-gray-900">{{ $document->senderUnit ? $document->senderUnit->name : 'Admin' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">To</p>
                                        <p class="text-sm font-medium text-gray-900">{{ $document->receivingUnit->name ?? '-' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('documents.view', ['id' => $document->id]) }}"
                                   class="px-4 py-2 rounded-lg bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition text-sm font-medium">
                                    View Details
                                </a>
                                @if ($document->file_path)
                                    <a href="{{ route('documents.download', ['id' => $document->id]) }}"
                                       class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition text-sm font-medium">
                                        Download
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Alternating Timeline -->
                    <div class="px-6 py-12">
                        <div class="timeline-wrapper">
                            
                            @php
                                $eventIndex = 0;
                            @endphp

                            <!-- Event 1: Created (LEFT) -->
                            <div class="timeline-event {{ $eventIndex % 2 == 0 ? 'left' : 'right' }}">
                                <div class="timeline-dot blue"></div>
                                <div class="timeline-card border-blue">
                                    <div class="card-header">
                                        <div class="card-icon bg-blue-light">
                                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="card-title text-blue-700">Created</h3>
                                            <p class="card-date">{{ $document->created_at->format('Y-m-d H:i:s') }}</p>
                                        </div>
                                    </div>
                                    <div class="card-details">
                                        <p><strong>at:</strong> {{ $document->senderUnit ? $document->senderUnit->name : 'Admin' }}</p>
                                        <p><strong>by:</strong> {{ $document->creator->name ?? ($document->senderUnit ? $document->senderUnit->name : 'System') }}</p>
                                    </div>
                                </div>
                            </div>

                            @php $eventIndex++; @endphp

                            <!-- Event 2: Sent (RIGHT) -->
                            <div class="timeline-event {{ $eventIndex % 2 == 0 ? 'left' : 'right' }}">
                                <div class="timeline-dot indigo"></div>
                                <div class="timeline-card border-indigo">
                                    <div class="card-header">
                                        <div class="card-icon bg-indigo-light">
                                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="card-title text-indigo-700">Sent</h3>
                                            <p class="card-date">{{ $document->created_at->format('Y-m-d H:i:s') }}</p>
                                        </div>
                                    </div>
                                    <div class="card-details">
                                        @php
                                            // The first unit to receive the document is either:
                                            // 1. The "from_unit" in the first forward record (because that's where it was before being forwarded)
                                            // 2. Or the current receiving_unit if no forwards exist
                                            if ($document->forwardHistory && $document->forwardHistory->count() > 0) {
                                                $firstReceivingUnit = $document->forwardHistory->sortBy('created_at')->first()->fromUnit->name;
                                            } else {
                                                $firstReceivingUnit = $document->receivingUnit->name ?? '-';
                                            }
                                        @endphp
                                        <p><strong>to:</strong> {{ $firstReceivingUnit }}</p>
                                    </div>
                                </div>
                            </div>

                            @php $eventIndex++; @endphp

                            <!-- Forwarding Events (Alternating) -->
                            @if($document->forwardHistory && $document->forwardHistory->count() > 0)
                                @foreach($document->forwardHistory as $forward)
                                    <div class="timeline-event {{ $eventIndex % 2 == 0 ? 'left' : 'right' }}">
                                        <div class="timeline-dot purple"></div>
                                        <div class="timeline-card border-purple">
                                            <div class="card-header">
                                                <div class="card-icon bg-purple-light">
                                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="card-title text-purple-700">Forwarded</h3>
                                                    <p class="card-date">{{ $forward->created_at->format('Y-m-d H:i:s') }}</p>
                                                </div>
                                            </div>
                                            <div class="card-details">
                                                <p><strong>from:</strong> {{ $forward->fromUnit->name ?? '-' }}</p>
                                                <p><strong>to:</strong> {{ $forward->toUnit->name ?? '-' }}</p>
                                                @if($forward->forwarded_by_user_id)
                                                    <p><strong>by:</strong> {{ $forward->forwardedBy->name ?? '-' }}</p>
                                                @endif
                                                @if($forward->notes)
                                                    <p class="mt-2"><strong>Notes:</strong> {{ $forward->notes }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @php $eventIndex++; @endphp
                                @endforeach
                            @endif

                            <!-- Final Status Event -->
                            @if($document->status == 'received')
                                <div class="timeline-event {{ $eventIndex % 2 == 0 ? 'left' : 'right' }}">
                                    <div class="timeline-dot green"></div>
                                    <div class="timeline-card border-green">
                                        <div class="card-header">
                                            <div class="card-icon bg-green-light">
                                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="card-title text-green-700">Received</h3>
                                                <p class="card-date">{{ $document->updated_at->format('Y-m-d H:i:s') }}</p>
                                            </div>
                                        </div>
                                        <div class="card-details">
                                            <p><strong>at:</strong> {{ $document->receivingUnit->name ?? '-' }}</p>
                                            @if($document->received_by)
                                                <p><strong>by:</strong> {{ $document->receivedBy->name ?? '-' }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @elseif($document->status == 'rejected')
                                <div class="timeline-event {{ $eventIndex % 2 == 0 ? 'left' : 'right' }}">
                                    <div class="timeline-dot red"></div>
                                    <div class="timeline-card border-red">
                                        <div class="card-header">
                                            <div class="card-icon bg-red-light">
                                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="card-title text-red-700">Rejected</h3>
                                                <p class="card-date">{{ $document->updated_at->format('Y-m-d H:i:s') }}</p>
                                            </div>
                                        </div>
                                        <div class="card-details">
                                            <p><strong>at:</strong> {{ $document->receivingUnit->name ?? '-' }}</p>
                                            @if($document->rejected_by)
                                                <p><strong>by:</strong> {{ $document->rejectedBy->name ?? '-' }}</p>
                                            @endif
                                            @if($document->rejection_reason)
                                                <p class="mt-2"><strong>Reason:</strong> {{ $document->rejection_reason }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="timeline-event {{ $eventIndex % 2 == 0 ? 'left' : 'right' }}">
                                    <div class="timeline-dot yellow"></div>
                                    <div class="timeline-card border-yellow">
                                        <div class="card-header">
                                            <div class="card-icon bg-yellow-light">
                                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="card-title text-yellow-700">Pending</h3>
                                                <p class="card-date">Awaiting action</p>
                                            </div>
                                        </div>
                                        <div class="card-details">
                                            <p><strong>at:</strong> {{ $document->receivingUnit->name ?? '-' }}</p>
                                            <p>Waiting to be received</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>

                </div>
                @endforeach

                <div class="mt-4 rounded-xl border border-slate-200 overflow-hidden">
                </div>
            @else
                <!-- No Results -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12">
                    <div class="flex flex-col items-center gap-4 text-center">
                        <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">No documents found</h3>
                            <p class="text-gray-600">
                                No document found with tracking number "<strong>{{ $searchQuery }}</strong>"
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        @else
            <!-- Initial State -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12">
                <div class="flex flex-col items-center gap-4 text-center">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Track Your Documents</h3>
                        <p class="text-gray-600 max-w-md">
                            Enter a tracking number in the search box above to view the complete journey and status of your document.
                        </p>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>

@endsection
