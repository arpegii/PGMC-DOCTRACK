@extends('layouts.app')

@section('header')
<div class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-6 py-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">
                    Document Details
                </h1>
            </div>
            <a href="{{ route('incoming.index') }}" 
               class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm font-semibold">
                ← Back to Documents
            </a>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto py-8 px-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        
        <!-- Document Header -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-8 py-6 border-b">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">
                        {{ $document->title }}
                    </h2>
                    <p class="text-sm text-gray-600">
                        Document #{{ $document->document_number }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    @if($document->resubmit_count > 0)
                        <span class="px-3 py-1 rounded-full bg-orange-100 text-orange-700 text-xs font-semibold border border-orange-200">
                            Resubmitted {{ $document->resubmit_count }}×
                        </span>
                    @endif
                    @if($document->status == 'incoming')
                        <span class="px-4 py-2 rounded-full bg-yellow-100 text-yellow-800 text-sm font-semibold">
                            Pending
                        </span>
                    @elseif($document->status == 'received')
                        <span class="px-4 py-2 rounded-full bg-green-100 text-green-800 text-sm font-semibold">
                            Received
                        </span>
                    @elseif($document->status == 'rejected')
                        <span class="px-4 py-2 rounded-full bg-red-100 text-red-800 text-sm font-semibold">
                            Rejected
                        </span>
                    @else
                        <span class="px-4 py-2 rounded-full bg-gray-100 text-gray-800 text-sm font-semibold">
                            {{ ucfirst($document->status) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Document Details -->
        <div class="px-8 py-6 space-y-6">
            
            <!-- Document Type -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Document Type</label>
                    <div class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-200">
                        <span class="text-gray-900 font-medium">{{ $document->document_type }}</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Date Created</label>
                    <div class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-200">
                        <span class="text-gray-900 font-medium">{{ $document->created_at->format('F d, Y h:i A') }}</span>
                    </div>
                </div>
            </div>

            <!-- Sender and Receiver -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Sender Unit</label>
                    <div class="px-4 py-3 bg-blue-50 rounded-lg border border-blue-200">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                            <span class="text-blue-900 font-semibold">{{ $document->senderUnit->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Receiving Unit</label>
                    <div class="px-4 py-3 bg-green-50 rounded-lg border border-green-200">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7l-4-4m0 0L8 7m4-4v12m0 6h.01M12 19a2 2 0 100-4 2 2 0 000 4z"/>
                            </svg>
                            <span class="text-green-900 font-semibold">{{ $document->receivingUnit->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- File Information -->
            @if($document->file_path)
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Attached File</label>
                <div class="px-4 py-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $document->file_name ?? basename($document->file_path) }}</p>
                                <p class="text-xs text-gray-500">Uploaded file</p>
                            </div>
                        </div>
                        <a href="{{ route('documents.download', ['id' => $document->id]) }}" 
                           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-semibold">
                            Download
                        </a>
                    </div>
                </div>
            </div>
            @else
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Attached File</label>
                <div class="px-4 py-4 bg-gray-50 rounded-lg border border-gray-200 text-center text-gray-500">
                    No file attached
                </div>
            </div>
            @endif

            <!-- Resubmit Notes (show if document has been resubmitted) -->
            @if($document->resubmit_count > 0 && $document->resubmit_notes)
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">Latest Resubmission Notes</label>
                <div class="bg-gradient-to-br from-orange-50 to-orange-100/50 border-2 border-orange-300 rounded-xl p-6 shadow-sm">
                    <div class="flex items-start gap-4 mb-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center shadow-lg">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <p class="text-lg font-bold text-orange-900 mb-1">
                                Resubmission #{{ $document->resubmit_count }}
                            </p>
                            <div class="flex items-center gap-2 text-orange-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                @if($document->last_resubmitted_at)
                                <p class="text-xs font-semibold">
                                    {{ $document->last_resubmitted_at->format('F d, Y') }} at {{ $document->last_resubmitted_at->format('h:i A') }}
                                    @if($document->lastResubmittedByUser)
                                        • Resubmitted by {{ $document->lastResubmittedByUser->name }}
                                    @endif
                                </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg border-2 border-orange-200 p-4 shadow-sm">
                        <p class="text-xs font-bold text-orange-800 uppercase tracking-wide mb-2">Resubmission Notes</p>
                        <p class="text-sm text-gray-800 whitespace-pre-wrap leading-relaxed">{{ $document->resubmit_notes }}</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Rejection Reason Alert (only show if document is rejected) -->
            @if($document->status == 'rejected' && $document->rejection_reason)
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">Rejection Details</label>
                <div class="bg-gradient-to-br from-red-50 to-red-100/50 border-2 border-red-300 rounded-xl p-6 shadow-sm">
                    <div class="flex items-start gap-4 mb-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center shadow-lg">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <p class="text-lg font-bold text-red-900 mb-1">
                                {{ $document->receivingUnit->name ?? 'Unknown Unit' }} rejected this document
                            </p>
                            <div class="flex items-center gap-2 text-red-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                @if($document->rejected_at)
                                <p class="text-xs font-semibold">
                                    {{ $document->rejected_at->format('F d, Y') }} at {{ $document->rejected_at->format('h:i A') }}
                                    @if($document->rejectedBy)
                                        • Rejected by {{ $document->rejectedBy->name }}
                                    @endif
                                </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg border-2 border-red-200 p-4 shadow-sm">
                        <p class="text-xs font-bold text-red-800 uppercase tracking-wide mb-2">Reason for Rejection</p>
                        <p class="text-sm text-gray-800 whitespace-pre-wrap leading-relaxed">{{ $document->rejection_reason }}</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Forwarding History Section -->
            @if($document->forwardHistory && $document->forwardHistory->count() > 0)
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">Forwarding History</label>
                <div class="space-y-3">
                    @foreach($document->forwardHistory->sortBy('created_at') as $forward)
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100/50 border-2 border-purple-300 rounded-xl p-6 shadow-sm">
                        <div class="flex items-start gap-4 mb-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center shadow-lg">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-lg font-bold text-purple-900 mb-1">
                                    {{ $forward->fromUnit->name ?? '-' }} → {{ $forward->toUnit->name ?? '-' }}
                                </p>
                                <div class="flex items-center gap-2 text-purple-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-xs font-semibold">
                                        {{ $forward->created_at->format('F d, Y') }} at {{ $forward->created_at->format('h:i A') }}
                                        @if($forward->forwardedBy)
                                            • Forwarded by {{ $forward->forwardedBy->name }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        @if($forward->notes)
                        <div class="bg-white rounded-lg border-2 border-purple-200 p-4 shadow-sm">
                            <p class="text-xs font-bold text-purple-800 uppercase tracking-wide mb-2">Forwarding Notes</p>
                            <p class="text-base text-gray-800 whitespace-pre-wrap leading-relaxed font-medium">{{ $forward->notes }}</p>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        <!-- Actions Footer -->
        <div class="px-8 py-4 bg-gray-50 border-t flex justify-between items-center">
            <a href="{{ route('incoming.index') }}" 
               class="px-4 py-2 text-gray-700 hover:text-gray-900 transition text-sm font-semibold">
                ← Back to List
            </a>
            
            @if($document->receiving_unit_id == auth()->user()->unit_id && $document->status == 'incoming')
            <form action="{{ route('documents.receive', ['id' => $document->id]) }}" method="POST">
                @csrf
                <button 
                    onclick="return confirm('Mark this document as received?')"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-semibold">
                    Mark as Received
                </button>
            </form>
            @endif
        </div>

    </div>
</div>
@endsection
