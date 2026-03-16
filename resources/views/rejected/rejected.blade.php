@extends('layouts.app')

@section('header')
<div class="page-hero">
    <div>
        <h1 class="page-title">Rejected Documents</h1>
        <p class="page-subtitle">Items returned with rejection reasons and timestamps</p>
    </div>
</div>
@endsection

@section('content')

<div class="pb-2">
    <form method="GET" action="{{ route('rejected.index') }}" class="filter-card px-4 py-4 md:px-5 border border-[#d8e2f0] bg-gradient-to-b from-white to-[#f8fbff]" data-live-search-form>
        <div class="flex flex-col gap-3 md:flex-row md:items-end">
            <div class="min-w-0 md:w-1/3">
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.06em] text-slate-500">Filter by unit</label>
                <select name="unit_id" class="form-select-modern" onchange="this.form.submit()">
                    <option value="">All units</option>
                    @foreach($filterUnits as $unit)
                        <option value="{{ $unit->id }}" {{ (string) $selectedUnitId === (string) $unit->id ? 'selected' : '' }}>
                            {{ $unit->name }}
                        </option>
                    @endforeach
                </select>
            </div>
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
                <thead class="table-head border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left">#</th>
                        <th class="px-2 py-4 text-center w-8"></th>
                        <th class="px-6 py-4 text-left">Document No.</th>
                        <th class="px-6 py-4 text-center">Document Title</th>
                        <th class="px-6 py-4 text-center">Sender Unit</th>
                        <th class="px-6 py-4 text-center">Type</th>
                        <th class="px-6 py-4 text-center">Receiving Unit</th>
                        <th class="px-6 py-4 text-center">Date Rejected</th>
                        <th class="px-6 py-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $document)
                    <tr class="table-row">
                        {{-- # --}}
                        <td class="px-6 py-4 font-medium text-slate-700 text-left">
                            {{ $documents->firstItem() + $loop->index }}
                        </td>

                        {{-- Rejection reason icon — tooltip is JS-rendered into <body> to escape overflow clipping --}}
                        <td class="px-2 py-4 text-center" style="width:32px;">
                            @if($document->rejection_reason)
                                <button
                                    type="button"
                                    class="rejection-icon-btn"
                                    data-rejection="{{ e($document->rejection_reason) }}"
                                    aria-label="View rejection reason"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                         style="width:17px;height:17px;display:block;">
                                        <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            @endif
                        </td>

                        {{-- Document No. --}}
                        <td class="px-6 py-4 font-semibold text-slate-900 text-left">
                            {{ $document->document_number }}
                        </td>

                        {{-- Document Title (plain — reason is now in the icon column) --}}
                        <td class="px-6 py-4 text-center font-medium text-slate-800">
                            {{ $document->title }}
                        </td>

                        <td class="px-6 py-4 text-center">
                            {{ $document->senderUnit->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="badge-chip bg-red-50 text-red-700">
                                {{ $document->document_type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            {{ $document->receivingUnit->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-slate-500 text-center">
                            {{ $document->updated_at->format('M d, Y h:i A') }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('documents.view', ['id' => $document->id]) }}"
                                   class="action-btn bg-slate-100 text-slate-700 hover:bg-slate-200">
                                    Details
                                </a>
                                @if ($document->file_path)
                                    <a href="{{ route('documents.download', ['id' => $document->id]) }}"
                                       class="action-btn bg-emerald-100 text-emerald-700 hover:bg-emerald-200">
                                        Download
                                    </a>
                                @endif
                                {{-- Only the sender unit (or admin) can resubmit --}}
                                @if(auth()->user()->isAdmin() || auth()->user()->unit_id === $document->sender_unit_id)
                                    <button
                                        type="button"
                                        onclick="openResubmitModal({
                                            id: {{ $document->id }},
                                            document_number: {{ Js::from($document->document_number) }},
                                            title: {{ Js::from($document->title) }},
                                            document_type: {{ Js::from($document->document_type) }},
                                            receiving_unit_id: '{{ $document->receiving_unit_id }}',
                                            receiving_unit_name: {{ Js::from($document->receivingUnit->name ?? '') }},
                                            rejection_reason: {{ Js::from($document->rejection_reason ?? '') }},
                                            file_name: {{ Js::from($document->file_name ?? ($document->file_path ? basename($document->file_path) : '')) }}
                                        })"
                                        class="action-btn bg-blue-100 text-blue-700 hover:bg-blue-200">
                                        Resubmit
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="table-empty">
                            <div class="flex flex-col items-center gap-2">
                                <span class="text-lg">📄</span>
                                <span>No rejected documents found</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('partials.pagination-controls', ['paginator' => $documents])
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     FLOATING UPLOAD BUTTON + UPLOAD MODAL (unchanged)
     ══════════════════════════════════════════════════════ -->
<div x-data="{ 
    open: false, 
    documentNumber: '',
    showSuccessUpload: false,
    async openModal() {
        this.open = true;
        await this.fetchDocumentNumber();
    },
    async fetchDocumentNumber() {
        try {
            const response = await fetch('{{ route('documents.next-number') }}');
            const data = await response.json();
            this.documentNumber = data.document_number;
        } catch (error) {
            console.error('Error fetching document number:', error);
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
}">

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

    <!-- MODAL BACKDROP -->
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
        <!-- MODAL CARD -->
        <div
            @click.stop
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90"
            class="bg-white shadow-2xl overflow-y-auto"
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
                    placeholder="Loading..."
                >
                <p class="text-xs text-gray-500 mt-1">Auto-generated document number</p>
            </div>

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
                           outline-none text-sm transition duration-200 hover:border-gray-400"
                >
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Receiving Unit <span class="text-red-500">*</span>
                </label>
                <div id="unit-picker" style="position: relative;">
                    <button
                        type="button"
                        id="unit-picker-btn"
                        onclick="toggleUnitDropdown(event)"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5
                               bg-white outline-none text-sm transition duration-200
                               hover:border-gray-400 text-left flex items-center justify-between"
                        style="color: #6b7280;"
                    >
                        <span id="unit-picker-label">Select Receiving Unit</span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <input type="hidden" name="receiving_unit_id" id="unit-hidden-input">
                    <div id="unit-dropdown" style="display:none;position:absolute;top:calc(100% + 4px);left:0;width:100%;background:white;border:1px solid #d1d5db;border-radius:0.625rem;box-shadow:0 8px 24px rgba(0,0,0,0.12);z-index:99999;overflow:hidden;max-height:220px;overflow-y:auto;">
                        @foreach($units as $unit)
                            @if($unit->id == auth()->user()->unit_id) @continue @endif
                            @if(in_array($unit->name, ['Resumption NCO','TOP NCO','Restoration NCO','Prior Years NCO','Pension Differential 18-19','Own Right NCO','Posthumous NCO','Retirement NCO','RSAB NCO','CDD NCO'])) @continue @endif
                            @if($unit->name === 'PAU')
                                <div class="unit-row" data-unit-id="{{ $unit->id }}" data-unit-name="PAU" data-has-flyout="pau" style="padding:0.6rem 1rem;font-size:0.875rem;color:#374151;cursor:pointer;display:flex;align-items:center;justify-content:space-between;transition:background 0.15s;">
                                    <span>PAU</span>
                                    <svg style="width:13px;height:13px;color:#9ca3af;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </div>
                            @elseif($unit->name === 'BGCU')
                                <div class="unit-row" data-unit-id="{{ $unit->id }}" data-unit-name="BGCU" data-has-flyout="bgcu" style="padding:0.6rem 1rem;font-size:0.875rem;color:#374151;cursor:pointer;display:flex;align-items:center;justify-content:space-between;transition:background 0.15s;">
                                    <span>BGCU</span>
                                    <svg style="width:13px;height:13px;color:#9ca3af;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </div>
                            @else
                                <div class="unit-row" data-unit-id="{{ $unit->id }}" data-unit-name="{{ $unit->name }}" style="padding:0.6rem 1rem;font-size:0.875rem;color:#374151;cursor:pointer;transition:background 0.15s;">
                                    {{ $unit->name }}
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-1">You cannot send to your own unit</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Document Type <span class="text-red-500">*</span>
                </label>
                <div id="doctype-picker" style="position: relative;">
                    <button type="button" id="doctype-picker-btn" onclick="toggleDoctypeDropdown(event)" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 bg-white outline-none text-sm transition duration-200 hover:border-gray-400 text-left flex items-center justify-between" style="color: #6b7280;">
                        <span id="doctype-picker-label">Select document type</span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <input type="hidden" name="document_type" id="doctype-hidden-input">
                    <div id="doctype-dropdown" style="display:none;position:absolute;top:calc(100% + 4px);left:0;width:100%;background:white;border:1px solid #d1d5db;border-radius:0.625rem;box-shadow:0 8px 24px rgba(0,0,0,0.12);z-index:99999;overflow:hidden;max-height:220px;overflow-y:auto;">
                        @foreach($documentTypes as $type)
                            <div class="doctype-row" data-value="{{ $type->name }}" style="padding:0.6rem 1rem;font-size:0.875rem;color:#374151;cursor:pointer;transition:background 0.15s;">{{ $type->name }}</div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Attach File <span class="text-red-500">*</span>
                </label>
                <input type="file" name="file" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:px-4 file:py-2 file:text-sm file:font-semibold hover:file:bg-blue-100 transition duration-200 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 cursor-pointer">
                <p class="text-xs text-gray-500 mt-1">Accepted: PDF, DOC, DOCX, JPG, PNG (Max: 25MB)</p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" @click="open = false" class="px-6 py-2.5 rounded-lg border-2 border-gray-300 text-gray-700 hover:bg-gray-50 transition duration-200 font-semibold text-sm">Cancel</button>
                <button type="submit" class="px-6 py-2.5 rounded-lg text-white font-semibold text-sm shadow-lg hover:shadow-xl transition duration-200 transform hover:-translate-y-0.5" style="background-color:#0B1F3A;">Upload</button>
            </div>
            </form>
        </div>
    </div>

    <!-- SUCCESS UPLOAD MODAL -->
    <div x-show="showSuccessUpload" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background-color: rgba(11, 31, 58, 0.6); backdrop-filter: blur(4px);" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div class="rounded-3xl shadow-2xl p-8 max-w-sm w-full mx-4 text-center" style="background-color: white;" x-transition:enter="transition ease-out duration-300 delay-75" x-transition:enter-start="opacity-0 scale-75" x-transition:enter-end="opacity-100 scale-100">
            <div class="mb-6">
                <div class="mx-auto w-20 h-20 rounded-full flex items-center justify-center shadow-lg animate-bounce-in" style="background: linear-gradient(to bottom right, #60a5fa, #3b82f6);">
                    <svg class="w-10 h-10" style="color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold mb-2" style="color: #111827;">Uploaded!</h3>
            <p class="text-sm" style="color: #6b7280;">Document has been uploaded successfully</p>
        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════════════════
     RESUBMIT MODAL
     ══════════════════════════════════════════════════════ -->
<div
    x-data="{
        open: false,
        showSuccess: false,
        doc: {
            id: '', document_number: '', title: '', document_type: '',
            receiving_unit_id: '', receiving_unit_name: '',
            rejection_reason: '', file_name: ''
        },
        openResubmit(detail) {
            this.doc = detail;
            this.open = true;
            this.$nextTick(() => {
                const unitInput = document.getElementById('resubmit-unit-hidden-input');
                const unitLabel = document.getElementById('resubmit-unit-picker-label');
                const typeInput = document.getElementById('resubmit-doctype-hidden-input');
                const typeLabel = document.getElementById('resubmit-doctype-picker-label');

                if (unitInput) unitInput.value = detail.receiving_unit_id || '';
                if (unitLabel) {
                    if (detail.receiving_unit_id) {
                        unitLabel.textContent = detail.receiving_unit_name || 'Select Receiving Unit';
                        unitLabel.style.color = '#111827';
                    } else {
                        unitLabel.textContent = 'Select Receiving Unit';
                        unitLabel.style.color = '#6b7280';
                    }
                }

                if (typeInput) typeInput.value = detail.document_type || '';
                if (typeLabel) {
                    if (detail.document_type) {
                        typeLabel.textContent = detail.document_type;
                        typeLabel.style.color = '#111827';
                    } else {
                        typeLabel.textContent = 'Select document type';
                        typeLabel.style.color = '#6b7280';
                    }
                }
            });
        },
        resubmitUrl() {
            return '/documents/' + this.doc.id + '/resubmit';
        },
        submitResubmit(event) {
            event.preventDefault();
            const unitInput = document.getElementById('resubmit-unit-hidden-input');
            const typeInput = document.getElementById('resubmit-doctype-hidden-input');
            if (!unitInput || !unitInput.value || !typeInput || !typeInput.value) {
                alert('Please select both Receiving Unit and Document Type.');
                return;
            }
            const backdrop = document.getElementById('resubmit-backdrop');
            if (backdrop) backdrop.dataset.submitting = '1';
            this.open = false;
            this.showSuccess = true;
            setTimeout(() => { event.target.submit(); }, 1500);
        }
    }"
    @open-resubmit.window="openResubmit($event.detail)"
>
    <!-- BACKDROP -->
    <div
        x-show="open"
        x-cloak
        id="resubmit-backdrop"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 z-[10000] flex items-center justify-center p-4"
        style="background-color: rgba(0,0,0,0.6); backdrop-filter: blur(4px);"
    >
        <!-- MODAL CARD -->
        <div
            @click.stop
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90"
            class="bg-white shadow-2xl overflow-y-auto"
            style="width:520px; max-height:92vh; border-radius:1.5rem;"
        >
            <!-- HEADER -->
            <div class="flex items-center justify-between px-5 py-3.5 border-b"
                 style="background: linear-gradient(to right, #eff6ff, #ffffff);">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0"
                         style="background:#dbeafe;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                             style="width:18px;height:18px;color:#2563eb;">
                            <path d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.405z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-gray-800">Resubmit Document</h2>
                        <p class="text-xs text-blue-600 font-medium" x-text="'#' + doc.document_number"></p>
                    </div>
                </div>
                <button @click="open = false" type="button"
                        class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- REJECTION REASON BANNER -->
            <div x-show="doc.rejection_reason"
                 class="mx-5 mt-4 rounded-xl border p-3"
                 style="background:#fff7f7; border-color:#fecaca;">
                <div class="flex items-center gap-2 mb-1">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                         style="width:14px;height:14px;color:#dc2626;flex-shrink:0;">
                        <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd"/>
                    </svg>
                    <span style="font-size:0.65rem;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;color:#dc2626;">
                        Reason for Rejection
                    </span>
                </div>
                <p class="text-xs text-red-800 leading-relaxed" x-text="doc.rejection_reason"
                   style="padding-left:20px;"></p>
            </div>

            <!-- ORIGINAL VALUES REFERENCE -->
            <div class="mx-5 mt-3 rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-3 py-2 bg-slate-50 border-b border-slate-200">
                    <span style="font-size:0.65rem;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;color:#64748b;">
                        Current Details (before changes)
                    </span>
                </div>
                <div class="grid grid-cols-2 divide-x divide-slate-100">
                    <div class="px-3 py-2">
                        <p class="text-xs text-slate-400 mb-0.5">Title</p>
                        <p class="text-xs font-medium text-slate-700 break-words" x-text="doc.title"></p>
                    </div>
                    <div class="px-3 py-2">
                        <p class="text-xs text-slate-400 mb-0.5">Type</p>
                        <p class="text-xs font-medium text-slate-700" x-text="doc.document_type"></p>
                    </div>
                    <div class="px-3 py-2 col-span-2 border-t border-slate-100">
                        <p class="text-xs text-slate-400 mb-0.5">Receiving Unit</p>
                        <p class="text-xs font-medium text-slate-700" x-text="doc.receiving_unit_name"></p>
                    </div>
                </div>
            </div>

            <!-- FORM -->
            <form
                method="POST"
                enctype="multipart/form-data"
                :action="resubmitUrl()"
                class="px-5 py-4 space-y-3.5"
                @submit="submitResubmit($event)"
            >
                @csrf
                @method('PATCH')

                {{-- Document Title --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Document Title <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="title"
                        :value="doc.title"
                        required
                        placeholder="Enter descriptive title"
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5
                               focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               outline-none text-sm transition hover:border-gray-400"
                    >
                </div>

                {{-- Receiving Unit + Document Type (side by side) --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                            Receiving Unit <span class="text-red-500">*</span>
                        </label>
                        <div id="resubmit-unit-picker" style="position: relative;">
                            <button
                                type="button"
                                id="resubmit-unit-picker-btn"
                                onclick="toggleResubmitUnitDropdown(event)"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5
                                       bg-white outline-none text-sm transition duration-200
                                       hover:border-gray-400 text-left flex items-center justify-between"
                                style="color: #6b7280;"
                            >
                                <span id="resubmit-unit-picker-label">Select Receiving Unit</span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <input type="hidden" name="receiving_unit_id" id="resubmit-unit-hidden-input">
                            <div id="resubmit-unit-dropdown" style="display:none;position:absolute;top:calc(100% + 4px);left:0;width:100%;background:white;border:1px solid #d1d5db;border-radius:0.625rem;box-shadow:0 8px 24px rgba(0,0,0,0.12);z-index:99999;overflow:hidden;max-height:220px;overflow-y:auto;">
                                @foreach($units as $unit)
                                    @if($unit->id == auth()->user()->unit_id) @continue @endif
                                    @if(in_array($unit->name, ['Resumption NCO','TOP NCO','Restoration NCO','Prior Years NCO','Pension Differential 18-19','Own Right NCO','Posthumous NCO','Retirement NCO','RSAB NCO','CDD NCO'])) @continue @endif
                                    @if($unit->name === 'PAU')
                                        <div class="resubmit-unit-row" data-unit-id="{{ $unit->id }}" data-unit-name="PAU" data-has-flyout="pau" style="padding:0.6rem 1rem;font-size:0.875rem;color:#374151;cursor:pointer;display:flex;align-items:center;justify-content:space-between;transition:background 0.15s;">
                                            <span>PAU</span>
                                            <svg style="width:13px;height:13px;color:#9ca3af;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </div>
                                    @elseif($unit->name === 'BGCU')
                                        <div class="resubmit-unit-row" data-unit-id="{{ $unit->id }}" data-unit-name="BGCU" data-has-flyout="bgcu" style="padding:0.6rem 1rem;font-size:0.875rem;color:#374151;cursor:pointer;display:flex;align-items:center;justify-content:space-between;transition:background 0.15s;">
                                            <span>BGCU</span>
                                            <svg style="width:13px;height:13px;color:#9ca3af;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </div>
                                    @else
                                        <div class="resubmit-unit-row" data-unit-id="{{ $unit->id }}" data-unit-name="{{ $unit->name }}" style="padding:0.6rem 1rem;font-size:0.875rem;color:#374151;cursor:pointer;transition:background 0.15s;">
                                            {{ $unit->name }}
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                            Document Type <span class="text-red-500">*</span>
                        </label>
                        <div id="resubmit-doctype-picker" style="position: relative;">
                            <button type="button" id="resubmit-doctype-picker-btn" onclick="toggleResubmitDoctypeDropdown(event)" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 bg-white outline-none text-sm transition duration-200 hover:border-gray-400 text-left flex items-center justify-between" style="color: #6b7280;">
                                <span id="resubmit-doctype-picker-label">Select document type</span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <input type="hidden" name="document_type" id="resubmit-doctype-hidden-input">
                            <div id="resubmit-doctype-dropdown" style="display:none;position:absolute;top:calc(100% + 4px);left:0;width:100%;background:white;border:1px solid #d1d5db;border-radius:0.625rem;box-shadow:0 8px 24px rgba(0,0,0,0.12);z-index:99999;overflow:hidden;max-height:220px;overflow-y:auto;">
                                @foreach($documentTypes as $type)
                                    <div class="resubmit-doctype-row" data-value="{{ $type->name }}" style="padding:0.6rem 1rem;font-size:0.875rem;color:#374151;cursor:pointer;transition:background 0.15s;">{{ $type->name }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Resubmit Notes --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Resubmit Notes
                        <span class="text-gray-400 font-normal normal-case ml-1">(describe what was changed)</span>
                    </label>
                    <textarea
                        name="resubmit_notes"
                        rows="2"
                        placeholder="e.g. Corrected document type and replaced file with updated version..."
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5
                               focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               outline-none text-sm transition hover:border-gray-400 resize-none"
                    ></textarea>
                </div>

                {{-- File Upload --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Attached File
                    </label>

                    {{-- Existing file chip --}}
                    <div x-show="doc.file_name"
                         class="flex items-center gap-2 mb-2 px-3 py-2 rounded-lg border border-slate-200 bg-slate-50">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                             style="width:15px;height:15px;color:#64748b;flex-shrink:0;">
                            <path fill-rule="evenodd" d="M5.625 1.5H9a3.75 3.75 0 013.75 3.75v1.875c0 1.036.84 1.875 1.875 1.875H16.5a3.75 3.75 0 013.75 3.75v7.875c0 1.035-.84 1.875-1.875 1.875H5.625a1.875 1.875 0 01-1.875-1.875V3.375c0-1.036.84-1.875 1.875-1.875zm6.905 9.97a.75.75 0 00-1.06 0l-3 3a.75.75 0 101.06 1.06l1.72-1.72V18a.75.75 0 001.5 0v-4.19l1.72 1.72a.75.75 0 101.06-1.06l-3-3z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-xs text-slate-600 font-medium truncate flex-1" x-text="doc.file_name"></span>
                        <span class="text-xs text-slate-400 flex-shrink-0">Current file</span>
                    </div>

                    <input
                        type="file"
                        name="file"
                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                        class="block w-full text-sm text-gray-600
                               file:mr-3 file:rounded-lg file:border-0
                               file:bg-blue-50 file:text-blue-700
                               file:px-3 file:py-2 file:text-xs file:font-semibold
                               hover:file:bg-blue-100 transition
                               border border-gray-300 rounded-lg
                               focus:ring-2 focus:ring-blue-500 cursor-pointer"
                    >
                    <p class="text-xs text-gray-400 mt-1">
                        Leave empty to keep the current file. PDF, DOC, DOCX, JPG, PNG (Max: 25MB)
                    </p>
                </div>

                <!-- FOOTER -->
                <div class="flex justify-end gap-3 pt-3 border-t">
                    <button
                        type="button"
                        @click="open = false"
                        class="px-5 py-2.5 rounded-lg border-2 border-gray-200 text-gray-600
                               hover:bg-gray-50 transition font-semibold text-sm"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2.5 rounded-lg text-white font-semibold text-sm
                               shadow hover:shadow-lg transition transform hover:-translate-y-0.5 flex items-center gap-2"
                        style="background: linear-gradient(135deg, #2563eb, #1d4ed8);"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:14px;height:14px;">
                            <path d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.405z"/>
                        </svg>
                        Resubmit Document
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- SUCCESS RESUBMIT MODAL -->
    <div
        x-show="showSuccess"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center"
        style="background-color: rgba(11,31,58,0.6); backdrop-filter: blur(4px);"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
    >
        <div
            class="rounded-3xl shadow-2xl p-8 max-w-sm w-full mx-4 text-center"
            style="background-color: white;"
            x-transition:enter="transition ease-out duration-300 delay-75"
            x-transition:enter-start="opacity-0 scale-75"
            x-transition:enter-end="opacity-100 scale-100"
        >
            <div class="mb-6">
                <div class="mx-auto w-20 h-20 rounded-full flex items-center justify-center shadow-lg animate-bounce-in"
                     style="background: linear-gradient(to bottom right, #34d399, #059669);">
                    <svg class="w-10 h-10" style="color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold mb-2" style="color: #111827;">Resubmitted!</h3>
            <p class="text-sm" style="color: #6b7280;">Document has been sent back for review</p>
        </div>
    </div>
</div>


<!-- PAU Flyout -->
<div id="pau-flyout" style="display:none;position:fixed;width:230px;background:white;border:1px solid #c7dcff;border-radius:0.625rem;box-shadow:0 8px 24px rgba(0,0,0,0.15);z-index:999999;overflow:hidden;">
    <div style="padding:0.5rem 1rem 0.4rem;font-size:0.7rem;font-weight:700;color:#1e5ba8;background:#f0f6ff;border-bottom:1px solid #c7dcff;letter-spacing:0.05em;">PAU SUB-UNITS</div>
    @foreach($units as $subUnit)
        @if(in_array($subUnit->name, ['Resumption NCO','TOP NCO','Restoration NCO','Prior Years NCO','Pension Differential 18-19','Own Right NCO']))
            <div class="flyout-item" data-unit-id="{{ $subUnit->id }}" data-unit-name="{{ $subUnit->name }}" style="padding:0.6rem 1rem;font-size:0.875rem;color:#374151;cursor:pointer;transition:background 0.15s;">{{ $subUnit->name }}</div>
        @endif
    @endforeach
</div>

<!-- BGCU Flyout -->
<div id="bgcu-flyout" style="display:none;position:fixed;width:210px;background:white;border:1px solid #c7dcff;border-radius:0.625rem;box-shadow:0 8px 24px rgba(0,0,0,0.15);z-index:999999;overflow:hidden;">
    <div style="padding:0.5rem 1rem 0.4rem;font-size:0.7rem;font-weight:700;color:#1e5ba8;background:#f0f6ff;border-bottom:1px solid #c7dcff;letter-spacing:0.05em;">BGCU SUB-UNITS</div>
    @foreach($units as $subUnit)
        @if(in_array($subUnit->name, ['Posthumous NCO','Retirement NCO','RSAB NCO','CDD NCO']))
            <div class="flyout-item" data-unit-id="{{ $subUnit->id }}" data-unit-name="{{ $subUnit->name }}" style="padding:0.6rem 1rem;font-size:0.875rem;color:#374151;cursor:pointer;transition:background 0.15s;">{{ $subUnit->name }}</div>
        @endif
    @endforeach
</div>


<style>
    [x-cloak] { display: none !important; }

    @keyframes bounce-in {
        0%   { transform: scale(0); opacity: 0; }
        50%  { transform: scale(1.1); }
        100% { transform: scale(1); opacity: 1; }
    }
    .animate-bounce-in {
        animation: bounce-in 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    /* ── Rejection icon button ──────────────────────────── */
    .rejection-icon-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 999px;
        background: #fef2f2;
        border: 1.5px solid #fca5a5;
        color: #ef4444;
        cursor: pointer;
        transition: background 0.15s, border-color 0.15s, transform 0.1s;
        padding: 0;
    }
    .rejection-icon-btn:hover {
        background: #fee2e2;
        border-color: #ef4444;
        transform: scale(1.12);
    }

    /* ── Body-appended tooltip ──────────────────────────── */
    #rejection-tooltip-popup {
        position: fixed;
        z-index: 999999;
        width: 260px;
        border-radius: 12px;
        overflow: hidden;
        border: 1.5px solid #fecaca;
        box-shadow: 0 8px 24px rgba(239,68,68,0.12), 0 2px 8px rgba(0,0,0,0.08);
        pointer-events: none;
        opacity: 0;
        transform: translateY(6px);
        transition: opacity 0.15s ease, transform 0.15s ease;
    }
    #rejection-tooltip-popup.visible {
        opacity: 1;
        transform: translateY(0);
    }
    #rejection-tooltip-popup .rtp-header {
        display: flex;
        align-items: center;
        gap: 6px;
        background: #fef2f2;
        color: #dc2626;
        font-size: 0.63rem;
        font-weight: 700;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        padding: 7px 11px;
        border-bottom: 1px solid #fecaca;
    }
    #rejection-tooltip-popup .rtp-body {
        background: #ffffff;
        color: #7f1d1d;
        font-size: 0.8rem;
        line-height: 1.55;
        padding: 9px 11px;
        margin: 0;
        word-break: break-word;
        white-space: pre-wrap;
        max-height: 140px;
        overflow-y: auto;
    }
    /* Scrollbar inside tooltip */
    #rejection-tooltip-popup .rtp-body::-webkit-scrollbar { width: 3px; }
    #rejection-tooltip-popup .rtp-body::-webkit-scrollbar-track { background: #fff7f7; }
    #rejection-tooltip-popup .rtp-body::-webkit-scrollbar-thumb { background: #fca5a5; border-radius: 4px; }

    /* Arrow pointing up toward the icon */
    #rejection-tooltip-popup .rtp-arrow {
        position: absolute;
        top: -7px;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 7px solid transparent;
        border-right: 7px solid transparent;
        border-bottom: 7px solid #fecaca;
    }
</style>

<script>
    let flyoutTimers = {};

    /* ── Rejection reason tooltip (body-appended, fixed position) ── */
    (function () {
        // Build the single shared tooltip element once
        const popup = document.createElement('div');
        popup.id = 'rejection-tooltip-popup';
        popup.innerHTML = `
            <div class="rtp-arrow"></div>
            <div class="rtp-header">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:12px;height:12px;flex-shrink:0;">
                    <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd"/>
                </svg>
                <span>Rejection Reason</span>
            </div>
            <p class="rtp-body"></p>
        `;
        document.addEventListener('DOMContentLoaded', () => document.body.appendChild(popup));

        let hideTimer = null;

        function showTooltip(btn) {
            clearTimeout(hideTimer);
            const reason = btn.dataset.rejection || '';
            popup.querySelector('.rtp-body').textContent = reason;

            // Position: centred below the button
            const rect   = btn.getBoundingClientRect();
            const tipW   = 260;
            let   left   = rect.left + rect.width / 2 - tipW / 2;
            const top    = rect.bottom + 10;

            // Keep inside viewport horizontally
            const margin = 8;
            if (left < margin) left = margin;
            if (left + tipW > window.innerWidth - margin) left = window.innerWidth - tipW - margin;

            // Reposition arrow to always point at the button
            const arrowLeft = (rect.left + rect.width / 2) - left;
            popup.querySelector('.rtp-arrow').style.left = Math.max(12, Math.min(tipW - 12, arrowLeft)) + 'px';
            popup.querySelector('.rtp-arrow').style.transform = 'translateX(-50%)';

            popup.style.left = left + 'px';
            popup.style.top  = top  + 'px';
            popup.classList.add('visible');
        }

        function hideTooltip() {
            hideTimer = setTimeout(() => popup.classList.remove('visible'), 80);
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.rejection-icon-btn').forEach(btn => {
                btn.addEventListener('mouseenter', () => showTooltip(btn));
                btn.addEventListener('mouseleave', hideTooltip);
                btn.addEventListener('focus',      () => showTooltip(btn));
                btn.addEventListener('blur',       hideTooltip);
            });
        });
    })();

    /**
     * Called by the plain onclick on each Resubmit button.
     * Fires a native window CustomEvent that the Alpine resubmit
     * component listens for via @open-resubmit.window.
     */
    function openResubmitModal(detail) {
        window.dispatchEvent(new CustomEvent('open-resubmit', { detail }));
    }

    function toggleUnitDropdown(e) {
        e.stopPropagation();
        const dropdown = document.getElementById('unit-dropdown');
        const isOpen   = dropdown.style.display === 'block';
        dropdown.style.display = isOpen ? 'none' : 'block';
        if (isOpen) { hideFlyout('pau-flyout'); hideFlyout('bgcu-flyout'); }
        document.getElementById('doctype-dropdown').style.display = 'none';
    }

    function toggleResubmitUnitDropdown(e) {
        e.stopPropagation();
        const dropdown = document.getElementById('resubmit-unit-dropdown');
        const isOpen   = dropdown.style.display === 'block';
        dropdown.style.display = isOpen ? 'none' : 'block';
        if (isOpen) { hideFlyout('pau-flyout'); hideFlyout('bgcu-flyout'); }
        document.getElementById('resubmit-doctype-dropdown').style.display = 'none';
    }

    function selectUnit(id, name) {
        document.getElementById('unit-hidden-input').value = id;
        const label = document.getElementById('unit-picker-label');
        label.textContent = name;
        label.style.color = '#111827';
        document.getElementById('unit-dropdown').style.display = 'none';
        hideFlyout('pau-flyout');
        hideFlyout('bgcu-flyout');
    }

    function selectResubmitUnit(id, name) {
        document.getElementById('resubmit-unit-hidden-input').value = id;
        const label = document.getElementById('resubmit-unit-picker-label');
        label.textContent = name;
        label.style.color = '#111827';
        document.getElementById('resubmit-unit-dropdown').style.display = 'none';
        hideFlyout('pau-flyout');
        hideFlyout('bgcu-flyout');
    }

    function hideFlyout(id) {
        clearTimeout(flyoutTimers[id]);
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    }

    function toggleDoctypeDropdown(e) {
        e.stopPropagation();
        const dropdown = document.getElementById('doctype-dropdown');
        const isOpen   = dropdown.style.display === 'block';
        dropdown.style.display = isOpen ? 'none' : 'block';
        document.getElementById('unit-dropdown').style.display = 'none';
        hideFlyout('pau-flyout');
        hideFlyout('bgcu-flyout');
    }

    function toggleResubmitDoctypeDropdown(e) {
        e.stopPropagation();
        const dropdown = document.getElementById('resubmit-doctype-dropdown');
        const isOpen   = dropdown.style.display === 'block';
        dropdown.style.display = isOpen ? 'none' : 'block';
        document.getElementById('resubmit-unit-dropdown').style.display = 'none';
        hideFlyout('pau-flyout');
        hideFlyout('bgcu-flyout');
    }

    function selectDoctype(value) {
        document.getElementById('doctype-hidden-input').value = value;
        const label = document.getElementById('doctype-picker-label');
        label.textContent = value;
        label.style.color = '#111827';
        document.getElementById('doctype-dropdown').style.display = 'none';
    }

    function selectResubmitDoctype(value) {
        document.getElementById('resubmit-doctype-hidden-input').value = value;
        const label = document.getElementById('resubmit-doctype-picker-label');
        label.textContent = value;
        label.style.color = '#111827';
        document.getElementById('resubmit-doctype-dropdown').style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function () {
        const pauFlyout  = document.getElementById('pau-flyout');
        const bgcuFlyout = document.getElementById('bgcu-flyout');
        document.body.appendChild(pauFlyout);
        document.body.appendChild(bgcuFlyout);

        document.querySelectorAll('#pau-flyout .flyout-item, #bgcu-flyout .flyout-item').forEach(item => {
            item.addEventListener('mouseenter', () => item.style.background = '#eff6ff');
            item.addEventListener('mouseleave', () => item.style.background = '');
            item.addEventListener('click', () => {
                const resubmitDropdown = document.getElementById('resubmit-unit-dropdown');
                if (resubmitDropdown && resubmitDropdown.style.display === 'block') {
                    selectResubmitUnit(item.dataset.unitId, item.dataset.unitName);
                } else {
                    selectUnit(item.dataset.unitId, item.dataset.unitName);
                }
            });
        });

        [pauFlyout, bgcuFlyout].forEach(flyout => {
            flyout.addEventListener('mouseenter', () => clearTimeout(flyoutTimers[flyout.id]));
            flyout.addEventListener('mouseleave', () => hideFlyout(flyout.id));
        });

        document.querySelectorAll('.unit-row').forEach(row => {
            row.addEventListener('mouseenter', () => {
                row.style.background = '#f3f4f6';
                const flyoutKey = row.dataset.hasFlyout;
                if (flyoutKey) {
                    const other = flyoutKey === 'pau' ? 'bgcu' : 'pau';
                    hideFlyout(other + '-flyout');
                    clearTimeout(flyoutTimers[flyoutKey + '-flyout']);
                    const rect   = row.getBoundingClientRect();
                    const flyout = document.getElementById(flyoutKey + '-flyout');
                    flyout.style.top  = rect.top + 'px';
                    flyout.style.left = (rect.right + 6) + 'px';
                    flyout.style.display = 'block';
                } else {
                    hideFlyout('pau-flyout');
                    hideFlyout('bgcu-flyout');
                }
            });
            row.addEventListener('mouseleave', () => {
                row.style.background = '';
                const flyoutKey = row.dataset.hasFlyout;
                if (flyoutKey) {
                    flyoutTimers[flyoutKey + '-flyout'] = setTimeout(() => hideFlyout(flyoutKey + '-flyout'), 120);
                }
            });
            row.addEventListener('click', () => selectUnit(row.dataset.unitId, row.dataset.unitName));
        });

        document.querySelectorAll('.resubmit-unit-row').forEach(row => {
            row.addEventListener('mouseenter', () => {
                row.style.background = '#f3f4f6';
                const flyoutKey = row.dataset.hasFlyout;
                if (flyoutKey) {
                    const other = flyoutKey === 'pau' ? 'bgcu' : 'pau';
                    hideFlyout(other + '-flyout');
                    clearTimeout(flyoutTimers[flyoutKey + '-flyout']);
                    const rect   = row.getBoundingClientRect();
                    const flyout = document.getElementById(flyoutKey + '-flyout');
                    flyout.style.top  = rect.top + 'px';
                    flyout.style.left = (rect.right + 6) + 'px';
                    flyout.style.display = 'block';
                } else {
                    hideFlyout('pau-flyout');
                    hideFlyout('bgcu-flyout');
                }
            });
            row.addEventListener('mouseleave', () => {
                row.style.background = '';
                const flyoutKey = row.dataset.hasFlyout;
                if (flyoutKey) {
                    flyoutTimers[flyoutKey + '-flyout'] = setTimeout(() => hideFlyout(flyoutKey + '-flyout'), 120);
                }
            });
            row.addEventListener('click', () => selectResubmitUnit(row.dataset.unitId, row.dataset.unitName));
        });

        document.querySelectorAll('.doctype-row').forEach(row => {
            row.addEventListener('mouseenter', () => row.style.background = '#f3f4f6');
            row.addEventListener('mouseleave', () => row.style.background = '');
            row.addEventListener('click', () => selectDoctype(row.dataset.value));
        });

        document.querySelectorAll('.resubmit-doctype-row').forEach(row => {
            row.addEventListener('mouseenter', () => row.style.background = '#f3f4f6');
            row.addEventListener('mouseleave', () => row.style.background = '');
            row.addEventListener('click', () => selectResubmitDoctype(row.dataset.value));
        });

        document.addEventListener('click', function (e) {
            const unitPicker    = document.getElementById('unit-picker');
            const doctypePicker = document.getElementById('doctype-picker');
            const resubmitUnitPicker    = document.getElementById('resubmit-unit-picker');
            const resubmitDoctypePicker = document.getElementById('resubmit-doctype-picker');
            if (unitPicker && !unitPicker.contains(e.target) &&
                !pauFlyout.contains(e.target) && !bgcuFlyout.contains(e.target)) {
                document.getElementById('unit-dropdown').style.display = 'none';
                hideFlyout('pau-flyout');
                hideFlyout('bgcu-flyout');
            }
            if (doctypePicker && !doctypePicker.contains(e.target)) {
                document.getElementById('doctype-dropdown').style.display = 'none';
            }
            if (resubmitUnitPicker && !resubmitUnitPicker.contains(e.target) &&
                !pauFlyout.contains(e.target) && !bgcuFlyout.contains(e.target)) {
                document.getElementById('resubmit-unit-dropdown').style.display = 'none';
                hideFlyout('pau-flyout');
                hideFlyout('bgcu-flyout');
            }
            if (resubmitDoctypePicker && !resubmitDoctypePicker.contains(e.target)) {
                document.getElementById('resubmit-doctype-dropdown').style.display = 'none';
            }
        });

        const modalBackdrop = document.querySelector('[x-show="open"]');
        if (modalBackdrop) {
            new MutationObserver(function () {
                if (modalBackdrop.style.display === 'none') {
                    document.getElementById('unit-hidden-input').value = '';
                    const unitLabel = document.getElementById('unit-picker-label');
                    unitLabel.textContent = 'Select Receiving Unit';
                    unitLabel.style.color = '#6b7280';
                    document.getElementById('unit-dropdown').style.display = 'none';
                    hideFlyout('pau-flyout');
                    hideFlyout('bgcu-flyout');

                    document.getElementById('doctype-hidden-input').value = '';
                    const doctypeLabel = document.getElementById('doctype-picker-label');
                    doctypeLabel.textContent = 'Select document type';
                    doctypeLabel.style.color = '#6b7280';
                    document.getElementById('doctype-dropdown').style.display = 'none';
                }
            }).observe(modalBackdrop, { attributes: true, attributeFilter: ['style'] });
        }

        const resubmitBackdrop = document.getElementById('resubmit-backdrop');
        if (resubmitBackdrop) {
            new MutationObserver(function () {
                if (resubmitBackdrop.style.display === 'none') {
                    if (resubmitBackdrop.dataset.submitting === '1') {
                        return;
                    }
                    document.getElementById('resubmit-unit-hidden-input').value = '';
                    const unitLabel = document.getElementById('resubmit-unit-picker-label');
                    unitLabel.textContent = 'Select Receiving Unit';
                    unitLabel.style.color = '#6b7280';
                    document.getElementById('resubmit-unit-dropdown').style.display = 'none';
                    hideFlyout('pau-flyout');
                    hideFlyout('bgcu-flyout');

                    document.getElementById('resubmit-doctype-hidden-input').value = '';
                    const doctypeLabel = document.getElementById('resubmit-doctype-picker-label');
                    doctypeLabel.textContent = 'Select document type';
                    doctypeLabel.style.color = '#6b7280';
                    document.getElementById('resubmit-doctype-dropdown').style.display = 'none';
                }
            }).observe(resubmitBackdrop, { attributes: true, attributeFilter: ['style'] });
        }
    });
</script>

@endsection
