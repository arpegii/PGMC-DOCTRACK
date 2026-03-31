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

    .timeline-dot.blue   { background: #3b82f6; color: #3b82f6; }
    .timeline-dot.indigo { background: #6366f1; color: #6366f1; }
    .timeline-dot.purple { background: #8b5cf6; color: #8b5cf6; }
    .timeline-dot.green  { background: #10b981; color: #10b981; }
    .timeline-dot.red    { background: #ef4444; color: #ef4444; }
    .timeline-dot.yellow { background: #f59e0b; color: #f59e0b; }
    .timeline-dot.orange { background: #f97316; color: #f97316; }

    /* Timeline Card */
    .timeline-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
        transition: all 0.3s ease;
        position: relative;
        min-width: 0;
    }

    .timeline-card:hover {
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
        transform: translateY(-2px);
    }

    /* Left-aligned card */
    .timeline-event.left {
        padding-right: 50%;
        padding-left: 0;
        text-align: right;
    }

    .timeline-event.left .timeline-card { margin-right: 2.5rem; }

    /* Right-aligned card */
    .timeline-event.right {
        padding-left: 50%;
        padding-right: 0;
        text-align: left;
    }

    .timeline-event.right .timeline-card { margin-left: 2.5rem; }

    /* Card Header */
    .card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }

    .timeline-event.left .card-header { flex-direction: row-reverse; }

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

    .card-details strong { color: #0f172a; font-weight: 600; }

    /* Border & Icon colours */
    .border-blue   { border: 2px solid #bfdbfe; }
    .border-indigo { border: 2px solid #c7d2fe; }
    .border-purple { border: 2px solid #ddd6fe; }
    .border-green  { border: 2px solid #bbf7d0; }
    .border-red    { border: 2px solid #fecaca; }
    .border-yellow { border: 2px solid #fde68a; }
    .border-orange { border: 2px solid #fed7aa; }

    .bg-blue-light   { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); }
    .bg-indigo-light { background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); }
    .bg-purple-light { background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%); }
    .bg-green-light  { background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); }
    .bg-red-light    { background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); }
    .bg-yellow-light { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); }
    .bg-orange-light { background: linear-gradient(135deg, #ffedd5 0%, #fed7aa 100%); }

    /* ── Resubmit changes table ── */
    .resubmit-changes-table {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
        font-size: 0.75rem;
        margin-top: 10px;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }
    .resubmit-changes-table colgroup col:nth-child(1) { width: 22%; }
    .resubmit-changes-table colgroup col:nth-child(2) { width: 39%; }
    .resubmit-changes-table colgroup col:nth-child(3) { width: 39%; }

    .resubmit-changes-table th {
        background: #f8faff;
        padding: 6px 10px;
        text-align: left;
        font-weight: 600;
        color: #64748b;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    .resubmit-changes-table td {
        padding: 6px 10px;
        border-bottom: 1px solid #f1f5f9;
        color: #475569;
        vertical-align: top;
        overflow-wrap: break-word;
        word-break: break-all;
        max-width: 0;
    }
    .resubmit-changes-table tr.changed td:nth-child(2) { color: #dc2626; }
    .resubmit-changes-table tr.changed td:nth-child(3) { color: #16a34a; font-weight: 600; }
    .resubmit-changes-table tr.changed { background: #fefce8; }

    /* Responsive */
    @media (max-width: 768px) {
        .timeline-wrapper::before { left: 20px; }

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

        .timeline-event.left .card-header { flex-direction: row; }
        .timeline-dot { left: 20px; transform: translateX(-50%); }
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
                               outline-none text-sm transition duration-200 hover:border-slate-400"
                        autofocus
                    >
                </div>
                <button
                    type="submit"
                    class="btn-primary-modern px-8 py-3 flex items-center gap-2 self-end"
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
                                <div class="flex items-center gap-3 mb-1">
                                    <h2 class="text-2xl font-bold text-gray-900">{{ $document->document_number }}</h2>
                                    @if(($document->resubmit_count ?? 0) > 0)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                                              style="background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:11px;height:11px;">
                                                <path fill-rule="evenodd" d="M4.755 10.059a7.5 7.5 0 0112.548-3.364l1.903 1.903h-3.183a.75.75 0 100 1.5h4.992a.75.75 0 00.75-.75V4.356a.75.75 0 00-1.5 0v3.18l-1.9-1.9A9 9 0 003.306 9.67a.75.75 0 101.45.388zm15.408 3.352a.75.75 0 00-.919.53 7.5 7.5 0 01-12.548 3.364l-1.902-1.903h3.183a.75.75 0 000-1.5H2.984a.75.75 0 00-.75.75v4.992a.75.75 0 001.5 0v-3.18l1.9 1.9a9 9 0 0015.059-4.035.75.75 0 00-.53-.918z" clip-rule="evenodd"/>
                                            </svg>
                                            Resubmitted {{ $document->resubmit_count }}×
                                        </span>
                                    @endif
                                </div>
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

                                $timelineEvents = collect();

                                // Forward history events
                                if ($document->forwardHistory) {
                                    foreach ($document->forwardHistory as $fwd) {
                                        $timelineEvents->push(['type' => 'forward', 'time' => $fwd->created_at, 'data' => $fwd]);
                                    }
                                }

                                // Resubmit history — each row generates both a rejection
                                // event (using rejected_at) and a resubmission event (using created_at)
                                // so both appear in the timeline in correct chronological order.
                                if ($document->resubmitHistory) {
                                    foreach ($document->resubmitHistory as $rsub) {

                                        // Rejection event: use stored rejected_at; fall back to 1 second
                                        // before the resubmission for rows created before the migration.
                                        $rejectedAt = $rsub->rejected_at ?? $rsub->created_at->clone()->subSecond();

                                        $timelineEvents->push([
                                            'type' => 'rejection',
                                            'time' => $rejectedAt,
                                            'data' => $rsub,
                                        ]);

                                        // Resubmission event
                                        $timelineEvents->push([
                                            'type' => 'resubmit',
                                            'time' => $rsub->created_at,
                                            'data' => $rsub,
                                        ]);
                                    }
                                }

                                // Sort everything chronologically
                                $timelineEvents = $timelineEvents->sortBy('time')->values();

                                // Determine "first receiving unit" for the Sent card
                                if ($document->forwardHistory && $document->forwardHistory->count() > 0) {
                                    $firstReceivingUnit = $document->forwardHistory->sortBy('created_at')->first()->fromUnit->name ?? '-';
                                } else {
                                    $firstReceivingUnit = $document->receivingUnit->name ?? '-';
                                }
                            @endphp

                            {{-- ── Event 1: Created ── --}}
                            <div class="timeline-event {{ $eventIndex % 2 == 0 ? 'left' : 'right' }}">
                                <div class="timeline-dot blue"></div>
                                <div class="timeline-card border-blue">
                                    <div class="card-header">
                                        <div class="card-icon bg-blue-light">
                                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
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

                            {{-- ── Event 2: Sent ── --}}
                            <div class="timeline-event {{ $eventIndex % 2 == 0 ? 'left' : 'right' }}">
                                <div class="timeline-dot indigo"></div>
                                <div class="timeline-card border-indigo">
                                    <div class="card-header">
                                        <div class="card-icon bg-indigo-light">
                                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="card-title text-indigo-700">Sent</h3>
                                            <p class="card-date">{{ $document->created_at->format('Y-m-d H:i:s') }}</p>
                                        </div>
                                    </div>
                                    <div class="card-details">
                                        <p><strong>to:</strong> {{ $firstReceivingUnit }}</p>
                                    </div>
                                </div>
                            </div>
                            @php $eventIndex++; @endphp

                            {{-- ── Interleaved: Rejections + Resubmissions + Forwards ── --}}
                            @foreach($timelineEvents as $event)

                                @if($event['type'] === 'rejection')
                                    @php $rsub = $event['data']; @endphp
                                    <div class="timeline-event {{ $eventIndex % 2 == 0 ? 'left' : 'right' }}">
                                        <div class="timeline-dot red"></div>
                                        <div class="timeline-card border-red">
                                            <div class="card-header">
                                                <div class="card-icon bg-red-light">
                                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="card-title text-red-700">Rejected</h3>
                                                    <p class="card-date">{{ ($rsub->rejected_at ?? $rsub->created_at->clone()->subSecond())->format('Y-m-d H:i:s') }}</p>
                                                </div>
                                            </div>
                                            <div class="card-details">
                                                @if($rsub->rejection_reason)
                                                    <p><strong>Reason:</strong> {{ $rsub->rejection_reason }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @php $eventIndex++; @endphp

                                @elseif($event['type'] === 'resubmit')
                                    @php $rsub = $event['data']; @endphp
                                    <div class="timeline-event {{ $eventIndex % 2 == 0 ? 'left' : 'right' }}">
                                        <div class="timeline-dot orange"></div>
                                        <div class="timeline-card border-orange">
                                            <div class="card-header">
                                                <div class="card-icon bg-orange-light">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                                         class="w-6 h-6 text-orange-600">
                                                        <path fill-rule="evenodd" d="M4.755 10.059a7.5 7.5 0 0112.548-3.364l1.903 1.903h-3.183a.75.75 0 100 1.5h4.992a.75.75 0 00.75-.75V4.356a.75.75 0 00-1.5 0v3.18l-1.9-1.9A9 9 0 003.306 9.67a.75.75 0 101.45.388zm15.408 3.352a.75.75 0 00-.919.53 7.5 7.5 0 01-12.548 3.364l-1.902-1.903h3.183a.75.75 0 000-1.5H2.984a.75.75 0 00-.75.75v4.992a.75.75 0 001.5 0v-3.18l1.9 1.9a9 9 0 0015.059-4.035.75.75 0 00-.53-.918z" clip-rule="evenodd"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <h3 class="card-title text-orange-700">Resubmitted</h3>
                                                    <p class="card-date">{{ $rsub->created_at->format('Y-m-d H:i:s') }} &nbsp;·&nbsp; Attempt #{{ $rsub->attempt }}</p>
                                                </div>
                                            </div>

                                            <div class="card-details space-y-3">

                                                <p><strong>by:</strong> {{ $rsub->resubmittedByUser->name ?? '-' }}</p>

                                                @if($rsub->resubmit_notes)
                                                    <div class="rounded-lg px-3 py-2"
                                                         style="background:#f0fdf4;border:1px solid #bbf7d0;text-align:left;">
                                                        <p class="text-xs font-bold text-emerald-700 uppercase tracking-wide mb-1">Resubmission Notes</p>
                                                        <p class="text-xs text-emerald-900 leading-relaxed">{{ $rsub->resubmit_notes }}</p>
                                                    </div>
                                                @endif

                                                @php
                                                    $rows = [
                                                        ['Title',          $rsub->previous_title,         $rsub->new_title],
                                                        ['Type',           $rsub->previous_document_type, $rsub->new_document_type],
                                                        ['Receiving Unit', $rsub->previousReceivingUnit->name ?? '—', $rsub->newReceivingUnit->name ?? '—'],
                                                        ['File',
                                                            $rsub->previous_file_name ?? ($rsub->previous_file_path ? basename($rsub->previous_file_path) : '—'),
                                                            $rsub->new_file_name      ?? ($rsub->new_file_path      ? basename($rsub->new_file_path)      : '—'),
                                                        ],
                                                    ];
                                                    $anyChanged = collect($rows)->some(fn($r) => $r[1] !== $r[2]);
                                                @endphp

                                                @if($anyChanged)
                                                <div style="text-align:left;">
                                                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Changes made</p>
                                                    <table class="resubmit-changes-table">
                                                        <colgroup>
                                                            <col style="width:20%">
                                                            <col style="width:40%">
                                                            <col style="width:40%">
                                                        </colgroup>
                                                        <thead>
                                                            <tr>
                                                                <th>Field</th>
                                                                <th>Before</th>
                                                                <th>After</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($rows as [$field, $before, $after])
                                                                @if($before !== $after)
                                                                    <tr class="changed">
                                                                        <td style="font-weight:600;color:#374151;white-space:nowrap;">{{ $field }}</td>
                                                                        <td title="{{ $before }}">{{ $before }}</td>
                                                                        <td title="{{ $after }}">{{ $after }}</td>
                                                                    </tr>
                                                                @endif
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                @else
                                                    <p class="text-xs text-slate-400 italic">Resubmitted without field changes</p>
                                                @endif

                                            </div>
                                        </div>
                                    </div>
                                    @php $eventIndex++; @endphp

                                @elseif($event['type'] === 'forward')
                                    @php $forward = $event['data']; @endphp
                                    <div class="timeline-event {{ $eventIndex % 2 == 0 ? 'left' : 'right' }}">
                                        <div class="timeline-dot purple"></div>
                                        <div class="timeline-card border-purple">
                                            <div class="card-header">
                                                <div class="card-icon bg-purple-light">
                                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
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

                                @endif

                            @endforeach

                            {{-- ── Final Status Event ── --}}
                            @if($document->status == 'received')
                                <div class="timeline-event {{ $eventIndex % 2 == 0 ? 'left' : 'right' }}">
                                    <div class="timeline-dot green"></div>
                                    <div class="timeline-card border-green">
                                        <div class="card-header">
                                            <div class="card-icon bg-green-light">
                                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
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
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
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
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
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

                        </div>{{-- end .timeline-wrapper --}}
                    </div>

                </div>
                @endforeach

                <div class="mt-4 rounded-xl border border-slate-200 overflow-hidden"></div>

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