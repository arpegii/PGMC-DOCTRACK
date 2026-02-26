@extends('layouts.app')

@section('header')

<div class="page-hero">
    <div>
        <h1 class="page-title">Incoming Documents</h1>
        <p class="page-subtitle">Review newly received files and take action quickly</p>
    </div>
</div>

@endsection

@section('content')

<!-- Error Messages -->
@if ($errors->any())
    <div class="py-4">
        <div class="panel-surface-soft border-red-200 bg-red-50 text-red-800 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

@if(auth()->user()->isAdmin())
    <div class="pb-2">
        <form method="GET" action="{{ route('incoming.index') }}" class="filter-card">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <div class="min-w-0 md:flex-1">
                    <select name="unit_id" class="form-select-modern">
                        <option value="">All units</option>
                        @foreach($filterUnits as $unit)
                            <option value="{{ $unit->id }}" {{ (string) $selectedUnitId === (string) $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2 md:justify-end md:ml-auto">
                    <button type="submit" class="btn-primary-modern">
                        Apply
                    </button>
                    <a href="{{ route('incoming.index', ['unit_id' => '', 'search' => request('search')]) }}" class="btn-secondary-modern">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
@endif

<div class="pb-2">
    <form method="GET" action="{{ route('incoming.index') }}" class="filter-card px-4 py-4 md:px-5 border border-[#d8e2f0] bg-gradient-to-b from-white to-[#f8fbff]" data-live-search-form>
        @if(auth()->user()->isAdmin() && $selectedUnitId)
            <input type="hidden" name="unit_id" value="{{ $selectedUnitId }}">
        @endif
        <div class="flex flex-col gap-3 md:flex-row md:items-end">
            <div class="min-w-0 md:flex-1">
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.06em] text-slate-500">Search document</label>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Document number, title, type, or unit"
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition duration-200 hover:border-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                >
            </div>
</div>
    </form>
</div>
<!-- CENTER WRAPPER -->
<div class="py-6">
    <div class="table-shell">
        <div class="overflow-x-auto modern-scrollbar">
            <table class="table-modern">

                <!-- Table Head -->
                <thead class="table-head border-b border-slate-200">
                    <tr>
                        <th class="table-head-cell text-left">#</th>
                        <th class="table-head-cell text-left">Document No.</th>
                        <th class="table-head-cell text-center">Document Title</th>
                        <th class="table-head-cell text-center">Sender Unit</th>
                        <th class="table-head-cell text-center">Type</th>
                        <th class="table-head-cell text-center">Receiving Unit</th>
                        <th class="table-head-cell text-center">Status</th>
                        <th class="table-head-cell text-center">Date</th>
                        <th class="table-head-cell text-center">Actions</th>
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody x-data="{ 
                    showConfirmReceive: false, 
                    showSuccessReceive: false,
                    showSuccessReject: false,
                    selectedDocId: null,
                    selectedDocNumber: '',
                    openRejectModal: false,
                    rejectionDocId: null,
                    rejectionDocNumber: '',
                    confirmReceive(docId, docNumber) {
                        this.selectedDocId = docId;
                        this.selectedDocNumber = docNumber;
                        this.showConfirmReceive = true;
                    },
                    openReject(docId, docNumber) {
                        this.rejectionDocId = docId;
                        this.rejectionDocNumber = docNumber;
                        this.openRejectModal = true;
                    },
                    submitReceive() {
                        this.showConfirmReceive = false;
                        this.showSuccessReceive = true;
                        setTimeout(() => {
                            const form = document.getElementById('receive-form-' + this.selectedDocId);
                            const formData = new FormData(form);
                            
                            fetch(form.action, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                }
                            }).then(response => {
                                window.location.reload();
                            });
                        }, 1500);
                    },
                    submitReject(event) {
                        this.openRejectModal = false;
                        this.showSuccessReject = true;
                        setTimeout(() => {
                            event.target.submit();
                        }, 1500);
                    }
                }">
                    @forelse ($documents as $document)
                    <tr class="table-row">

                        <td class="table-cell font-medium text-slate-700 text-left">
                            {{ $documents->firstItem() + $loop->index }}
                        </td>

                        <td class="table-cell font-semibold text-slate-900 text-left">
                            {{ $document->document_number }}
                        </td>

                        <!-- Title -->
                        <td class="table-cell text-center">
                            {{ $document->title }}
                        </td>

                        <!-- Sender Unit -->
                        <td class="table-cell text-center">
                            {{ $document->senderUnit->name ?? '-' }}
                        </td>

                        <!-- Type -->
                        <td class="table-cell text-center">
                            <span class="badge-chip bg-blue-50 text-blue-700">
                                {{ $document->document_type }}
                            </span>
                        </td>

                        <!-- Receiving Unit -->
                        <td class="table-cell text-center">
                            {{ $document->receivingUnit->name ?? '-' }}
                        </td>

                        <!-- Status -->
                        <td class="table-cell text-center">
                            @if($document->status == 'incoming')
                                <span class="badge-chip bg-amber-50 text-amber-700">
                                    Pending
                                </span>
                            @elseif($document->status == 'received')
                                <span class="badge-chip bg-emerald-50 text-emerald-700">
                                    Received
                                </span>
                            @elseif($document->status == 'rejected')
                                <span class="badge-chip bg-red-50 text-red-700">
                                    Rejected
                                </span>
                            @else
                                <span class="badge-chip bg-slate-100 text-slate-700">
                                    {{ ucfirst($document->status) }}
                                </span>
                            @endif
                        </td>

                        <!-- Date -->
                        <td class="table-cell text-slate-500 text-center">
                            {{ $document->created_at->format('M d, Y h:i A') }}
                        </td>

                        <!-- Actions -->
                        <td class="table-cell">
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem; max-width: 200px; margin: 0 auto;">
                                
                                <!-- View Button -->
                                <a href="{{ route('documents.view', ['id' => $document->id]) }}"
                                   class="action-btn action-btn-neutral text-center whitespace-nowrap">
                                    Details
                                </a>

                                <!-- Download Button -->
                                @if ($document->file_path)
                                    <a href="{{ route('documents.download', ['id' => $document->id]) }}"
                                       class="action-btn action-btn-success text-center whitespace-nowrap">
                                        Download
                                    </a>
                                @else
                                    <div class="action-btn action-btn-disabled text-center whitespace-nowrap">
                                        No File
                                    </div>
                                @endif

                                <!-- Receive Button - ONLY if user can receive -->
                                @if($document->receiving_unit_id == auth()->user()->unit_id && $document->status == 'incoming')
                                    <!-- Hidden form for receiving -->
                                    <form id="receive-form-{{ $document->id }}" 
                                          action="{{ route('documents.receive', ['id' => $document->id]) }}" 
                                          method="POST" 
                                          class="hidden">
                                        @csrf
                                    </form>

                                    <button
                                        type="button"
                                        @click="confirmReceive({{ $document->id }}, '{{ $document->document_number }}')"
                                        class="action-btn action-btn-primary whitespace-nowrap">
                                        Receive
                                    </button>

                                    <!-- Reject Button -->
                                    <button
                                        type="button"
                                        @click="openReject({{ $document->id }}, '{{ $document->document_number }}')"
                                        class="action-btn action-btn-danger whitespace-nowrap">
                                        Reject
                                    </button>
                                @endif

                            </div>
                        </td>      
                    </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="text-lg">📄</span>
                                    <span>No incoming documents found</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse

                    <!-- RECEIVE CONFIRMATION MODAL -->
                    <tr x-show="showConfirmReceive" x-cloak style="display: none;">
                        <td colspan="9">
                            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/55 backdrop-blur-md"
                                 @click.self="showConfirmReceive = false"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100">
                                
                                <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-md w-full overflow-hidden"
                                     @click.stop
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 scale-90 translate-y-4"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0">
                                    
                                    <div class="px-6 py-5 border-b border-emerald-200 bg-gradient-to-br from-emerald-50 via-emerald-100/70 to-emerald-50">
                                        <div class="flex items-center gap-3">
                                            <div class="w-11 h-11 rounded-xl bg-white/70 border border-emerald-200 flex items-center justify-center text-emerald-700 shadow-sm">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                            </div>
                                            <div class="flex-1">
                                                <h3 class="text-xl font-semibold text-slate-900">Receive Document</h3>
                                                <p class="text-sm text-slate-600 mt-0.5">Confirm that this document has been received.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Body -->
                                    <div class="px-6 py-5">
                                        <p class="text-sm text-slate-600">
                                            Receive
                                            <span class="inline-flex items-center rounded-md bg-slate-100 border border-slate-200 px-2 py-1 text-slate-900 font-semibold" x-text="selectedDocNumber"></span>
                                            now?
                                        </p>
                                    </div>

                                    <!-- Footer -->
                                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-2.5">
                                        <button @click="showConfirmReceive = false"
                                                type="button"
                                                class="btn-secondary-modern">
                                            Cancel
                                        </button>
                                        <button @click="submitReceive()"
                                                type="button"
                                                class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                                            Confirm
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- SUCCESS RECEIVE MODAL -->
                    <tr x-show="showSuccessReceive" x-cloak style="display: none;">
                        <td colspan="9">
                            <div class="fixed inset-0 z-50 flex items-center justify-center" 
                                 style="background-color: rgba(11, 31, 58, 0.6); backdrop-filter: blur(4px);"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100">
                                
                                <div class="rounded-3xl shadow-2xl p-8 max-w-sm w-full mx-4 text-center" style="background-color: white;"
                                     x-transition:enter="transition ease-out duration-300 delay-75"
                                     x-transition:enter-start="opacity-0 scale-75"
                                     x-transition:enter-end="opacity-100 scale-100">
                                    
                                    <!-- Animated checkmark -->
                                    <div class="mb-6">
                                        <div class="mx-auto w-20 h-20 rounded-full flex items-center justify-center shadow-lg animate-bounce-in" 
                                             style="background: linear-gradient(to bottom right, #34d399, #10b981);">
                                            <svg class="w-10 h-10" style="color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Success message -->
                                    <h3 class="text-2xl font-bold mb-2" style="color: #111827;">Received!</h3>
                                    <p class="text-sm" style="color: #6b7280;">
                                        Document has been marked as received
                                    </p>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- SUCCESS REJECT MODAL -->
                    <tr x-show="showSuccessReject" x-cloak style="display: none;">
                        <td colspan="9">
                            <div class="fixed inset-0 z-50 flex items-center justify-center" 
                                 style="background-color: rgba(11, 31, 58, 0.6); backdrop-filter: blur(4px);"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100">
                                
                                <div class="rounded-3xl shadow-2xl p-8 max-w-sm w-full mx-4 text-center" style="background-color: white;"
                                     x-transition:enter="transition ease-out duration-300 delay-75"
                                     x-transition:enter-start="opacity-0 scale-75"
                                     x-transition:enter-end="opacity-100 scale-100">
                                    
                                    <!-- Animated X mark -->
                                    <div class="mb-6">
                                        <div class="mx-auto w-20 h-20 rounded-full flex items-center justify-center shadow-lg animate-bounce-in" 
                                             style="background: linear-gradient(to bottom right, #f87171, #ef4444);">
                                            <svg class="w-10 h-10" style="color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Success message -->
                                    <h3 class="text-2xl font-bold mb-2" style="color: #111827;">Rejected</h3>
                                    <p class="text-sm" style="color: #6b7280;">
                                        Document has been rejected
                                    </p>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- REJECTION MODAL -->
                    <tr x-show="openRejectModal" x-cloak style="display: none;">
                        <td colspan="9">
                            <div class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
                                 @click.self="openRejectModal = false"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100">
                                
                                <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden w-full max-w-2xl max-h-[90vh]"
                                     @click.stop
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 scale-90"
                                     x-transition:enter-end="opacity-100 scale-100">
                                    
                                    <!-- Header -->
                                    <div class="flex items-center justify-between px-6 py-5 border-b border-red-200 bg-gradient-to-r from-red-50 via-rose-50/70 to-white">
                                        <div>
                                            <h2 class="text-2xl font-semibold text-slate-900">Reject Document</h2>
                                            <p class="text-sm text-slate-600 mt-1" x-text="rejectionDocNumber"></p>
                                        </div>

                                        <button
                                            @click="openRejectModal = false"
                                            type="button"
                                            class="w-10 h-10 flex items-center justify-center rounded-xl border border-transparent hover:border-slate-300 hover:bg-white text-slate-500 hover:text-slate-700 transition duration-200"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Form -->
                                    <form 
                                        :action="'/documents/' + rejectionDocId + '/reject'" 
                                        method="POST"
                                        class="px-6 py-5"
                                        @submit.prevent="submitReject($event)"
                                    >
                                        @csrf

                                        <div class="mb-5">
                                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                                Reason for Rejection <span class="text-red-500">*</span>
                                            </label>
                                            <textarea
                                                name="rejection_reason"
                                                required
                                                rows="5"
                                                placeholder="Please provide a detailed reason for rejecting this document..."
                                                class="w-full rounded-xl border border-slate-300 px-4 py-3
                                                       focus:ring-2 focus:ring-red-500 focus:border-red-500 
                                                       outline-none text-sm transition duration-200
                                                       hover:border-slate-400 resize-none"
                                            ></textarea>
                                            <p class="text-xs text-slate-500 mt-2">This reason will be visible to the sender.</p>
                                        </div>

                                        <!-- Warning -->
                                        <div class="mb-5 p-4 bg-red-50/80 border border-red-200 rounded-xl">
                                            <div class="flex gap-3">
                                                <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                </svg>
                                                <div>
                                                    <p class="text-base font-semibold text-red-800">Are you sure?</p>
                                                    <p class="text-sm text-red-700 mt-0.5">This action cannot be undone. The document will be marked as rejected.</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Footer -->
                                        <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                                            <button
                                                type="button"
                                                @click="openRejectModal = false"
                                                class="btn-secondary-modern"
                                            >
                                                Cancel
                                            </button>

                                            <button
                                                type="submit"
                                                class="inline-flex items-center justify-center rounded-xl bg-red-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700 transition"
                                            >
                                                Reject Document
                                            </button>
                                        </div>
                                    </form>

                                </div>
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>

        @include('partials.pagination-controls', ['paginator' => $documents])
    </div>

</div>

<!-- FLOATING UPLOAD BUTTON + MODAL -->
<div x-data="{ 
    open: false, 
    documentNumber: '',
    loading: false,
    error: '',
    showSuccessUpload: false,
    async openModal() {
        console.log('Modal opening...');
        this.open = true;
        this.loading = true;
        this.error = '';
        this.documentNumber = 'Loading...';
        await this.fetchDocumentNumber();
    },
    async fetchDocumentNumber() {
        console.log('Starting fetch...');
        
        try {
            const url = '{{ route("documents.next-number") }}';
            console.log('Fetching from URL:', url);
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                }
            });
            
            console.log('Response received:', response);
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Error response:', errorText);
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Data received:', data);
            
            this.documentNumber = data.document_number;
            this.loading = false;
            console.log('Document number set to:', this.documentNumber);
            
        } catch (error) {
            console.error('Fetch error:', error);
            this.documentNumber = 'Error: ' + error.message;
            this.error = error.message;
            this.loading = false;
        }
    },
    submitUpload(event) {
        event.preventDefault();
        this.open = false;
        this.showSuccessUpload = true;
        setTimeout(() => {
            event.target.submit();
        }, 1500);
    }
}" x-init="console.log('Alpine component initialized')">

    <!-- FLOATING BUTTON -->
    <button
        @click="openModal()"
        type="button"
        style="
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            background-color: #0B1F3A;
            color: #ffffff;
            padding: 14px 22px;
            border-radius: 9999px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.35);
            font-weight: 600;
        "
    >
        ＋ Document
    </button>

    <!-- MODAL BACKDROP + MODAL (CENTERED) -->
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 z-[10000] flex items-center justify-center p-4"
        style="background-color: rgba(0, 0, 0, 0.6); backdrop-filter: blur(4px);"
    >

        <!-- MODAL CARD (PERFECTLY CENTERED - SQUARE) -->
        <div
            @click.stop
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90"
            class="bg-white rounded-5xl shadow-2xl overflow-y-auto"
            style="width: 500px; max-height: 90vh; border-radius: 2rem;"
        >

            <!-- HEADER -->
            <div class="flex items-center justify-between px-2 py-2 border-b bg-gradient-to-r from-gray-50 to-white">
                <h2 class="text-l font-semibold text-gray-800 mb-0.5 px-4">
                    Upload New Document
                </h2>

                <button
                    @click="open = false"
                    type="button"
                    class="w-9 h-9 flex items-center justify-center rounded-full 
                           hover:bg-gray-200 text-gray-500 hover:text-gray-700 transition duration-200 mb-0.5 px-4"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- FORM -->
            <form
                action="{{ route('documents.store') }}"
                method="POST"
                enctype="multipart/form-data"
                class="px-6 py-4 space-y-2.5"
                @submit="submitUpload($event)"
            >
            @csrf

            <!-- Document Number (Auto-generated, Read-only) -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Document Number <span class="text-red-500">*</span>
                </label>
                <input
                    name="document_number"
                    x-model="documentNumber"
                    required
                    readonly
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5
                           bg-gray-50 text-gray-600 cursor-not-allowed
                           focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
                           outline-none text-sm transition duration-200"
                    :placeholder="loading ? 'Loading...' : 'Auto-generated'"
                >
                <p class="text-xs text-gray-500 mt-1">Auto-generated document number</p>
                <p x-show="error" class="text-xs text-red-500 mt-1" x-text="'Error: ' + error"></p>
            </div>

            <!-- Document Title -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Document Title <span class="text-red-500">*</span>
                </label>
                <input
                    name="title"
                    required
                    placeholder="Enter descriptive title"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5
                           focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
                           outline-none text-sm transition duration-200
                           hover:border-gray-400"
                >
            </div>

            <!-- Receiving Unit -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Receiving Unit <span class="text-red-500">*</span>
                </label>
                <select
                    name="receiving_unit_id"
                    required
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5
                           bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
                           outline-none text-sm transition duration-200
                           hover:border-gray-400"
                >
                    <option value="">Select Receiving Unit</option>
                    @foreach($units as $unit)
                        @if($unit->id != auth()->user()->unit_id)
                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                        @endif
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">You cannot send to your own unit</p>
            </div>

            <!-- Document Type -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Document Type <span class="text-red-500">*</span>
                </label>
                <select
                    name="document_type"
                    required
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5
                           bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
                           outline-none text-sm transition duration-200
                           hover:border-gray-400"
                >
                    <option value="">Select document type</option>
                    <option>Birth Certificate</option>
                    <option>Marriage Certificate</option>
                    <option>Clearance</option>
                    <option>Memorandum</option>
                    <option>Letter</option>
                    <option>Report</option>
                    <option>Others</option>
                </select>
            </div>

            <!-- File Upload -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Attach File <span class="text-red-500">*</span>
                </label>

                <div class="relative">
                    <input
                        type="file"
                        name="file"
                        required
                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                        class="block w-full text-sm text-gray-600
                               file:mr-4 file:rounded-lg file:border-0
                               file:bg-blue-50 file:text-blue-700
                               file:px-4 file:py-2 file:text-sm file:font-semibold
                               hover:file:bg-blue-100 transition duration-200
                               border border-gray-300 rounded-lg
                               focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               cursor-pointer"
                    >
                </div>
                <p class="text-xs text-gray-500 mt-1">Accepted: PDF, DOC, DOCX, JPG, PNG (Max: 25MB)</p>
            </div>

            <!-- FOOTER -->
            <div class="flex justify-end gap-3 pt-4 border-t">

                <button
                    type="button"
                    @click="open = false"
                    class="px-6 py-2.5 rounded-lg border-2 border-gray-300
                           text-gray-700 hover:bg-gray-50 transition duration-200 
                           font-semibold text-sm"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    class="px-6 py-2.5 rounded-lg text-white font-semibold text-sm
                           shadow-lg hover:shadow-xl transition duration-200
                           transform hover:-translate-y-0.5"
                    style="background-color:#0B1F3A;"
                >
                    Upload
                </button>

            </div>
            </form>

        </div>
    </div>

    <!-- SUCCESS UPLOAD MODAL -->
    <div x-show="showSuccessUpload" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center" 
         style="background-color: rgba(11, 31, 58, 0.6); backdrop-filter: blur(4px);"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">
        
        <div class="rounded-3xl shadow-2xl p-8 max-w-sm w-full mx-4 text-center" 
             style="background-color: white;"
             x-transition:enter="transition ease-out duration-300 delay-75"
             x-transition:enter-start="opacity-0 scale-75"
             x-transition:enter-end="opacity-100 scale-100">
            
            <!-- Animated checkmark -->
            <div class="mb-6">
                <div class="mx-auto w-20 h-20 rounded-full flex items-center justify-center shadow-lg animate-bounce-in" 
                     style="background: linear-gradient(to bottom right, #60a5fa, #3b82f6);">
                    <svg class="w-10 h-10" style="color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>

            <!-- Success message -->
            <h3 class="text-2xl font-bold mb-2" style="color: #111827;">Uploaded!</h3>
            <p class="text-sm" style="color: #6b7280;">
                Document has been uploaded successfully
            </p>
        </div>
    </div>

</div>

<!-- Add this to your CSS or in a style tag -->
<style>
    [x-cloak] { 
        display: none !important; 
    }

    @keyframes bounce-in {
        0% {
            transform: scale(0);
            opacity: 0;
        }
        50% {
            transform: scale(1.1);
        }
        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .animate-bounce-in {
        animation: bounce-in 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }
</style>

@endsection









