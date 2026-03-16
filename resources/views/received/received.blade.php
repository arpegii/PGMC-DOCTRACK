@extends('layouts.app')

@section('header')
<div class="page-hero">
    <div>
        <h1 class="page-title">Received Documents</h1>
        <p class="page-subtitle">Documents already acknowledged by your unit</p>
    </div>
</div>
@endsection

@section('content')

<div class="pb-2">
    <form method="GET" action="{{ route('received.index') }}" class="filter-card px-4 py-4 md:px-5 border border-[#d8e2f0] bg-gradient-to-b from-white to-[#f8fbff]" data-live-search-form>
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
                        <th class="px-6 py-4 text-center">#</th>
                        <th class="px-6 py-4 text-left">Document No.</th>
                        <th class="px-6 py-4 text-center">Document Title</th>
                        <th class="px-6 py-4 text-center">Sender Unit</th>
                        <th class="px-6 py-4 text-center">Type</th>
                        <th class="px-6 py-4 text-center">Receiving Unit</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Date</th>
                        <th class="px-6 py-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $document)
                        <tr class="table-row">
                            <td class="px-6 py-4 font-medium text-slate-700 text-center">
                                {{ $documents->firstItem() + $loop->index }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-900 text-left">
                                {{ $document->document_number }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                {{ $document->title }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                {{ $document->senderUnit->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="badge-chip bg-blue-50 text-blue-700">
                                    {{ $document->document_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                {{ $document->receivingUnit->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="badge-chip bg-emerald-50 text-emerald-700">Received</span>
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-center">
                                {{ $document->updated_at->format('M d, Y h:i A') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2" x-data>
                                    <a href="{{ route('documents.view', ['id' => $document->id]) }}"
                                       class="action-btn bg-slate-100 text-slate-700 hover:bg-slate-200">
                                        Details
                                    </a>
                                    <button
                                        data-document-id="{{ $document->id }}"
                                        data-document-number="{{ $document->document_number }}"
                                        data-document-title="{{ $document->title }}"
                                        onclick="openForwardModalFromBtn(this)"
                                        type="button"
                                        class="action-btn bg-blue-100 text-blue-700 hover:bg-blue-200">
                                        Forward
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="table-empty">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="text-lg">📄</span>
                                    <span>No received documents found</span>
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

<!-- FLOATING UPLOAD BUTTON + MODAL -->
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

            <!-- Document Number -->
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
                           outline-none text-sm transition duration-200 hover:border-gray-400"
                >
            </div>

            <!-- Receiving Unit -->
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

                    <div
                        id="unit-dropdown"
                        style="
                            display: none;
                            position: absolute;
                            top: calc(100% + 4px);
                            left: 0;
                            width: 100%;
                            background: white;
                            border: 1px solid #d1d5db;
                            border-radius: 0.625rem;
                            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
                            z-index: 99999;
                            overflow: hidden;
                            max-height: 220px;
                            overflow-y: auto;
                        "
                    >
                        @foreach($units as $unit)
                            @if($unit->id == auth()->user()->unit_id)
                                @continue
                            @endif
                            @if(in_array($unit->name, [
                                'Resumption NCO', 'TOP NCO', 'Restoration NCO',
                                'Prior Years NCO', 'Pension Differential 18-19', 'Own Right NCO',
                                'Posthumous NCO', 'Retirement NCO', 'RSAB NCO', 'CDD NCO'
                            ]))
                                @continue
                            @endif

                            @if($unit->name === 'PAU')
                                <div
                                    class="unit-row"
                                    data-unit-id="{{ $unit->id }}"
                                    data-unit-name="PAU"
                                    data-has-flyout="pau"
                                    style="padding:0.6rem 1rem; font-size:0.875rem; color:#374151; cursor:pointer; display:flex; align-items:center; justify-content:space-between; transition:background 0.15s;"
                                >
                                    <span>PAU</span>
                                    <svg style="width:13px;height:13px;color:#9ca3af;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            @elseif($unit->name === 'BGCU')
                                <div
                                    class="unit-row"
                                    data-unit-id="{{ $unit->id }}"
                                    data-unit-name="BGCU"
                                    data-has-flyout="bgcu"
                                    style="padding:0.6rem 1rem; font-size:0.875rem; color:#374151; cursor:pointer; display:flex; align-items:center; justify-content:space-between; transition:background 0.15s;"
                                >
                                    <span>BGCU</span>
                                    <svg style="width:13px;height:13px;color:#9ca3af;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            @else
                                <div
                                    class="unit-row"
                                    data-unit-id="{{ $unit->id }}"
                                    data-unit-name="{{ $unit->name }}"
                                    style="padding:0.6rem 1rem; font-size:0.875rem; color:#374151; cursor:pointer; transition:background 0.15s;"
                                >
                                    {{ $unit->name }}
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <p class="text-xs text-gray-500 mt-1">You cannot send to your own unit</p>
            </div>

            <!-- Document Type -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Document Type <span class="text-red-500">*</span>
                </label>

                <div id="doctype-picker" style="position: relative;">
                    <button
                        type="button"
                        id="doctype-picker-btn"
                        onclick="toggleDoctypeDropdown(event)"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5
                               bg-white outline-none text-sm transition duration-200
                               hover:border-gray-400 text-left flex items-center justify-between"
                        style="color: #6b7280;"
                    >
                        <span id="doctype-picker-label">Select document type</span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <input type="hidden" name="document_type" id="doctype-hidden-input">

                    <div
                        id="doctype-dropdown"
                        style="
                            display: none;
                            position: absolute;
                            top: calc(100% + 4px);
                            left: 0;
                            width: 100%;
                            background: white;
                            border: 1px solid #d1d5db;
                            border-radius: 0.625rem;
                            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
                            z-index: 99999;
                            overflow: hidden;
                            max-height: 220px;
                            overflow-y: auto;
                        "
                    >
                        @foreach($documentTypes as $type)
                            <div
                                class="doctype-row"
                                data-value="{{ $type->name }}"
                                style="padding:0.6rem 1rem; font-size:0.875rem; color:#374151; cursor:pointer; transition:background 0.15s;"
                            >
                                {{ $type->name }}
                            </div>
                        @endforeach
                    </div>
                </div>
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
            <div class="mb-6">
                <div class="mx-auto w-20 h-20 rounded-full flex items-center justify-center shadow-lg animate-bounce-in"
                     style="background: linear-gradient(to bottom right, #60a5fa, #3b82f6);">
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

<!-- FORWARD DOCUMENT MODAL -->
<div x-data="{ 
    openForward: false,
    selectedDocumentId: null,
    selectedDocumentNumber: '',
    selectedDocumentTitle: '',
    selectedUnit: '',
    showSuccessForward: false,
    forwardedUnitName: '',
    async submitForward(event) {
        event.preventDefault();
        try {
            const form = document.getElementById('forward-form');
            const unitInput = document.getElementById('forward-unit-hidden-input');
            if (!unitInput || !unitInput.value) {
                alert('Please select a unit to forward to.');
                return;
            }
            const unitLabel = document.getElementById('forward-unit-picker-label');
            this.forwardedUnitName = unitLabel ? unitLabel.textContent : '';
            const backdrop = document.querySelector('[x-show=openForward]');
            if (backdrop) backdrop.dataset.submitting = '1';
            this.openForward = false;
            this.showSuccessForward = true;
            setTimeout(() => { form.submit(); }, 1500);
        } catch (error) {
            console.error('Error forwarding document:', error);
            alert('An error occurred. Please try again.');
        }
    }
}"
@open-forward-modal.window="
    openForward = true;
    selectedDocumentId = $event.detail.documentId;
    selectedDocumentNumber = $event.detail.documentNumber;
    selectedDocumentTitle = $event.detail.documentTitle;
    selectedUnit = '';
"
>

    <!-- FORWARD MODAL BACKDROP -->
    <div
        x-show="openForward"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="openForward = false"
        class="fixed inset-0 z-[10000] flex items-center justify-center p-4"
        style="background-color: rgba(0, 0, 0, 0.6); backdrop-filter: blur(4px);"
    >
        <div
            @click.stop
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90"
            class="bg-white rounded-2xl shadow-2xl overflow-hidden"
            style="width: 500px; max-height: 90vh;"
        >
            <!-- HEADER -->
            <div class="flex items-center justify-between px-6 py-4 border-b bg-gradient-to-r from-blue-50 to-white">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">Forward Document</h2>
                    <p class="text-xs text-gray-600 mt-0.5" x-text="selectedDocumentNumber"></p>
                </div>
                <button
                    @click="openForward = false"
                    type="button"
                    class="w-9 h-9 flex items-center justify-center rounded-full 
                           hover:bg-gray-200 text-gray-500 hover:text-gray-700 transition duration-200"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- FORM -->
            <form
                id="forward-form"
                :action="`/documents/${selectedDocumentId}/forward`"
                method="POST"
                @submit="submitForward($event)"
                class="px-6 py-4 space-y-4"
            >
                @csrf

                <!-- Document Info -->
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <p class="text-xs font-semibold text-gray-600 uppercase mb-1">Document Title</p>
                    <p class="text-sm text-gray-800" x-text="selectedDocumentTitle"></p>
                </div>

                <!-- Forward to Unit — custom picker with flyout -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Forward to Unit <span class="text-red-500">*</span>
                    </label>

                    <div id="forward-unit-picker" style="position: relative;">
                        <button
                            type="button"
                            id="forward-unit-picker-btn"
                            onclick="toggleForwardUnitDropdown(event)"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3
                                   bg-white outline-none text-sm transition duration-200
                                   hover:border-gray-400 text-left flex items-center justify-between"
                            style="color: #6b7280;"
                        >
                            <span id="forward-unit-picker-label">Select unit to forward to</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <input type="hidden" name="forward_to_unit_id" id="forward-unit-hidden-input"
                               x-model="selectedUnit">

                        <div
                            id="forward-unit-dropdown"
                            style="
                                display: none;
                                position: absolute;
                                top: calc(100% + 4px);
                                left: 0;
                                width: 100%;
                                background: white;
                                border: 1px solid #d1d5db;
                                border-radius: 0.625rem;
                                box-shadow: 0 8px 24px rgba(0,0,0,0.12);
                                z-index: 99999;
                                overflow: hidden;
                                max-height: 220px;
                                overflow-y: auto;
                            "
                        >
                            @foreach($allUnits as $unit)
                                @if($unit->id == auth()->user()->unit_id)
                                    @continue
                                @endif
                                @if(in_array($unit->name, [
                                    'Resumption NCO', 'TOP NCO', 'Restoration NCO',
                                    'Prior Years NCO', 'Pension Differential 18-19', 'Own Right NCO',
                                    'Posthumous NCO', 'Retirement NCO', 'RSAB NCO', 'CDD NCO'
                                ]))
                                    @continue
                                @endif

                                @if($unit->name === 'PAU')
                                    <div
                                        class="forward-unit-row"
                                        data-unit-id="{{ $unit->id }}"
                                        data-unit-name="PAU"
                                        data-has-flyout="fwd-pau"
                                        style="padding:0.6rem 1rem; font-size:0.875rem; color:#374151; cursor:pointer; display:flex; align-items:center; justify-content:space-between; transition:background 0.15s;"
                                    >
                                        <span>PAU</span>
                                        <svg style="width:13px;height:13px;color:#9ca3af;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </div>
                                @elseif($unit->name === 'BGCU')
                                    <div
                                        class="forward-unit-row"
                                        data-unit-id="{{ $unit->id }}"
                                        data-unit-name="BGCU"
                                        data-has-flyout="fwd-bgcu"
                                        style="padding:0.6rem 1rem; font-size:0.875rem; color:#374151; cursor:pointer; display:flex; align-items:center; justify-content:space-between; transition:background 0.15s;"
                                    >
                                        <span>BGCU</span>
                                        <svg style="width:13px;height:13px;color:#9ca3af;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </div>
                                @else
                                    <div
                                        class="forward-unit-row"
                                        data-unit-id="{{ $unit->id }}"
                                        data-unit-name="{{ $unit->name }}"
                                        style="padding:0.6rem 1rem; font-size:0.875rem; color:#374151; cursor:pointer; transition:background 0.15s;"
                                    >
                                        {{ $unit->name }}
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1.5">Choose the unit that will receive this document</p>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Notes (Optional)
                    </label>
                    <textarea
                        name="notes"
                        rows="3"
                        placeholder="Add any forwarding notes or instructions..."
                        class="w-full rounded-lg border border-gray-300 px-4 py-3
                               focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
                               outline-none text-sm transition duration-200
                               hover:border-gray-400 resize-none"
                    ></textarea>
                </div>

                <!-- FOOTER -->
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button
                        type="button"
                        @click="openForward = false"
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
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                            Forward
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- SUCCESS FORWARD MODAL -->
    <div x-show="showSuccessForward"
         x-cloak
         class="fixed inset-0 z-[10001] flex items-center justify-center"
         style="background-color: rgba(11, 31, 58, 0.6); backdrop-filter: blur(4px);"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">
        <div class="rounded-3xl shadow-2xl p-8 max-w-sm w-full mx-4 text-center"
             style="background-color: white;"
             x-transition:enter="transition ease-out duration-300 delay-75"
             x-transition:enter-start="opacity-0 scale-75"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="mb-6">
                <div class="mx-auto w-20 h-20 rounded-full flex items-center justify-center shadow-lg animate-bounce-in"
                     style="background: linear-gradient(to bottom right, #60a5fa, #3b82f6);">
                    <svg class="w-10 h-10" style="color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold mb-2" style="color: #111827;">Forwarded!</h3>
            <p class="text-sm mb-1" style="color: #6b7280;">
                <span class="font-semibold" x-text="selectedDocumentNumber"></span>
            </p>
            <p class="text-sm" style="color: #6b7280;">
                has been forwarded to <span class="font-semibold" x-text="forwardedUnitName"></span>
            </p>
        </div>
    </div>

</div>

<!-- PAU Flyout (upload modal) -->
<div id="pau-flyout" style="display:none;position:fixed;width:230px;background:white;border:1px solid #c7dcff;border-radius:0.625rem;box-shadow:0 8px 24px rgba(0,0,0,0.15);z-index:999999;overflow:hidden;">
    <div style="padding:0.5rem 1rem 0.4rem;font-size:0.7rem;font-weight:700;color:#1e5ba8;background:#f0f6ff;border-bottom:1px solid #c7dcff;letter-spacing:0.05em;">PAU SUB-UNITS</div>
    @foreach($units as $subUnit)
        @if(in_array($subUnit->name, ['Resumption NCO','TOP NCO','Restoration NCO','Prior Years NCO','Pension Differential 18-19','Own Right NCO']))
            <div class="flyout-item" data-unit-id="{{ $subUnit->id }}" data-unit-name="{{ $subUnit->name }}" style="padding:0.6rem 1rem;font-size:0.875rem;color:#374151;cursor:pointer;transition:background 0.15s;">{{ $subUnit->name }}</div>
        @endif
    @endforeach
</div>

<!-- BGCU Flyout (upload modal) -->
<div id="bgcu-flyout" style="display:none;position:fixed;width:210px;background:white;border:1px solid #c7dcff;border-radius:0.625rem;box-shadow:0 8px 24px rgba(0,0,0,0.15);z-index:999999;overflow:hidden;">
    <div style="padding:0.5rem 1rem 0.4rem;font-size:0.7rem;font-weight:700;color:#1e5ba8;background:#f0f6ff;border-bottom:1px solid #c7dcff;letter-spacing:0.05em;">BGCU SUB-UNITS</div>
    @foreach($units as $subUnit)
        @if(in_array($subUnit->name, ['Posthumous NCO','Retirement NCO','RSAB NCO','CDD NCO']))
            <div class="flyout-item" data-unit-id="{{ $subUnit->id }}" data-unit-name="{{ $subUnit->name }}" style="padding:0.6rem 1rem;font-size:0.875rem;color:#374151;cursor:pointer;transition:background 0.15s;">{{ $subUnit->name }}</div>
        @endif
    @endforeach
</div>

<!-- PAU Flyout (forward modal) -->
<div id="fwd-pau-flyout" style="display:none;position:fixed;width:230px;background:white;border:1px solid #c7dcff;border-radius:0.625rem;box-shadow:0 8px 24px rgba(0,0,0,0.15);z-index:999999;overflow:hidden;">
    <div style="padding:0.5rem 1rem 0.4rem;font-size:0.7rem;font-weight:700;color:#1e5ba8;background:#f0f6ff;border-bottom:1px solid #c7dcff;letter-spacing:0.05em;">PAU SUB-UNITS</div>
    @foreach($allUnits as $subUnit)
        @if(in_array($subUnit->name, ['Resumption NCO','TOP NCO','Restoration NCO','Prior Years NCO','Pension Differential 18-19','Own Right NCO']))
            <div class="fwd-flyout-item" data-unit-id="{{ $subUnit->id }}" data-unit-name="{{ $subUnit->name }}" style="padding:0.6rem 1rem;font-size:0.875rem;color:#374151;cursor:pointer;transition:background 0.15s;">{{ $subUnit->name }}</div>
        @endif
    @endforeach
</div>

<!-- BGCU Flyout (forward modal) -->
<div id="fwd-bgcu-flyout" style="display:none;position:fixed;width:210px;background:white;border:1px solid #c7dcff;border-radius:0.625rem;box-shadow:0 8px 24px rgba(0,0,0,0.15);z-index:999999;overflow:hidden;">
    <div style="padding:0.5rem 1rem 0.4rem;font-size:0.7rem;font-weight:700;color:#1e5ba8;background:#f0f6ff;border-bottom:1px solid #c7dcff;letter-spacing:0.05em;">BGCU SUB-UNITS</div>
    @foreach($allUnits as $subUnit)
        @if(in_array($subUnit->name, ['Posthumous NCO','Retirement NCO','RSAB NCO','CDD NCO']))
            <div class="fwd-flyout-item" data-unit-id="{{ $subUnit->id }}" data-unit-name="{{ $subUnit->name }}" style="padding:0.6rem 1rem;font-size:0.875rem;color:#374151;cursor:pointer;transition:background 0.15s;">{{ $subUnit->name }}</div>
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
</style>

<script>
    let flyoutTimers = {};

    // ── Shared flyout helpers ────────────────────────────────────────────────
    function hideFlyout(id) {
        clearTimeout(flyoutTimers[id]);
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    }

    function showFlyoutNext(row, flyoutId) {
        const rect   = row.getBoundingClientRect();
        const flyout = document.getElementById(flyoutId);
        flyout.style.top  = rect.top + 'px';
        flyout.style.left = (rect.right + 6) + 'px';
        flyout.style.display = 'block';
    }

    // ── Upload modal — unit picker ───────────────────────────────────────────
    function toggleUnitDropdown(e) {
        e.stopPropagation();
        const dropdown = document.getElementById('unit-dropdown');
        const isOpen   = dropdown.style.display === 'block';
        dropdown.style.display = isOpen ? 'none' : 'block';
        if (isOpen) { hideFlyout('pau-flyout'); hideFlyout('bgcu-flyout'); }
        document.getElementById('doctype-dropdown').style.display = 'none';
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

    // ── Upload modal — doctype picker ────────────────────────────────────────
    function toggleDoctypeDropdown(e) {
        e.stopPropagation();
        const dropdown = document.getElementById('doctype-dropdown');
        const isOpen   = dropdown.style.display === 'block';
        dropdown.style.display = isOpen ? 'none' : 'block';
        document.getElementById('unit-dropdown').style.display = 'none';
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

    // ── Forward modal — unit picker ──────────────────────────────────────────
    function openForwardModalFromBtn(btn) {
        if (!btn) return;
        openForwardModal({
            documentId: btn.dataset.documentId,
            documentNumber: btn.dataset.documentNumber || '',
            documentTitle: btn.dataset.documentTitle || ''
        });
    }

    function openForwardModal(detail) {
        window.dispatchEvent(new CustomEvent('open-forward-modal', { detail }));
    }

    function toggleForwardUnitDropdown(e) {
        e.stopPropagation();
        const dropdown = document.getElementById('forward-unit-dropdown');
        const isOpen   = dropdown.style.display === 'block';
        dropdown.style.display = isOpen ? 'none' : 'block';
        if (isOpen) { hideFlyout('fwd-pau-flyout'); hideFlyout('fwd-bgcu-flyout'); }
    }

    function selectForwardUnit(id, name) {
        document.getElementById('forward-unit-hidden-input').value = id;
        // Also update Alpine x-model by dispatching an input event
        const input = document.getElementById('forward-unit-hidden-input');
        input.dispatchEvent(new Event('input', { bubbles: true }));
        const label = document.getElementById('forward-unit-picker-label');
        label.textContent = name;
        label.style.color = '#111827';
        document.getElementById('forward-unit-dropdown').style.display = 'none';
        hideFlyout('fwd-pau-flyout');
        hideFlyout('fwd-bgcu-flyout');
    }

    // ── Init ─────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {

        // Move all flyouts to <body>
        ['pau-flyout', 'bgcu-flyout', 'fwd-pau-flyout', 'fwd-bgcu-flyout'].forEach(id => {
            const el = document.getElementById(id);
            if (el) document.body.appendChild(el);
        });

        // ── Upload modal flyout items
        document.querySelectorAll('#pau-flyout .flyout-item, #bgcu-flyout .flyout-item').forEach(item => {
            item.addEventListener('mouseenter', () => item.style.background = '#eff6ff');
            item.addEventListener('mouseleave', () => item.style.background = '');
            item.addEventListener('click', () => selectUnit(item.dataset.unitId, item.dataset.unitName));
        });

        ['pau-flyout', 'bgcu-flyout'].forEach(id => {
            const el = document.getElementById(id);
            el.addEventListener('mouseenter', () => clearTimeout(flyoutTimers[id]));
            el.addEventListener('mouseleave', () => hideFlyout(id));
        });

        // ── Upload modal unit rows
        document.querySelectorAll('.unit-row').forEach(row => {
            row.addEventListener('mouseenter', () => {
                row.style.background = '#f3f4f6';
                const fk = row.dataset.hasFlyout;
                if (fk) {
                    hideFlyout(fk === 'pau' ? 'bgcu-flyout' : 'pau-flyout');
                    clearTimeout(flyoutTimers[fk + '-flyout']);
                    showFlyoutNext(row, fk + '-flyout');
                } else {
                    hideFlyout('pau-flyout');
                    hideFlyout('bgcu-flyout');
                }
            });
            row.addEventListener('mouseleave', () => {
                row.style.background = '';
                const fk = row.dataset.hasFlyout;
                if (fk) flyoutTimers[fk + '-flyout'] = setTimeout(() => hideFlyout(fk + '-flyout'), 120);
            });
            row.addEventListener('click', () => selectUnit(row.dataset.unitId, row.dataset.unitName));
        });

        // ── Doctype rows
        document.querySelectorAll('.doctype-row').forEach(row => {
            row.addEventListener('mouseenter', () => row.style.background = '#f3f4f6');
            row.addEventListener('mouseleave', () => row.style.background = '');
            row.addEventListener('click',      () => selectDoctype(row.dataset.value));
        });

        // ── Forward modal flyout items
        document.querySelectorAll('#fwd-pau-flyout .fwd-flyout-item, #fwd-bgcu-flyout .fwd-flyout-item').forEach(item => {
            item.addEventListener('mouseenter', () => item.style.background = '#eff6ff');
            item.addEventListener('mouseleave', () => item.style.background = '');
            item.addEventListener('click', () => selectForwardUnit(item.dataset.unitId, item.dataset.unitName));
        });

        ['fwd-pau-flyout', 'fwd-bgcu-flyout'].forEach(id => {
            const el = document.getElementById(id);
            el.addEventListener('mouseenter', () => clearTimeout(flyoutTimers[id]));
            el.addEventListener('mouseleave', () => hideFlyout(id));
        });

        // ── Forward modal unit rows
        document.querySelectorAll('.forward-unit-row').forEach(row => {
            row.addEventListener('mouseenter', () => {
                row.style.background = '#f3f4f6';
                const fk = row.dataset.hasFlyout;
                if (fk) {
                    hideFlyout(fk === 'fwd-pau' ? 'fwd-bgcu-flyout' : 'fwd-pau-flyout');
                    clearTimeout(flyoutTimers[fk + '-flyout']);
                    showFlyoutNext(row, fk + '-flyout');
                } else {
                    hideFlyout('fwd-pau-flyout');
                    hideFlyout('fwd-bgcu-flyout');
                }
            });
            row.addEventListener('mouseleave', () => {
                row.style.background = '';
                const fk = row.dataset.hasFlyout;
                if (fk) flyoutTimers[fk + '-flyout'] = setTimeout(() => hideFlyout(fk + '-flyout'), 120);
            });
            row.addEventListener('click', () => selectForwardUnit(row.dataset.unitId, row.dataset.unitName));
        });

        // ── Close dropdowns on outside click
        document.addEventListener('click', function (e) {
            const pauFlyout    = document.getElementById('pau-flyout');
            const bgcuFlyout   = document.getElementById('bgcu-flyout');
            const fwdPauFlyout = document.getElementById('fwd-pau-flyout');
            const fwdBgcuFlyout= document.getElementById('fwd-bgcu-flyout');

            const unitPicker        = document.getElementById('unit-picker');
            const doctypePicker     = document.getElementById('doctype-picker');
            const forwardUnitPicker = document.getElementById('forward-unit-picker');

            if (unitPicker && !unitPicker.contains(e.target) &&
                !pauFlyout.contains(e.target) && !bgcuFlyout.contains(e.target)) {
                document.getElementById('unit-dropdown').style.display = 'none';
                hideFlyout('pau-flyout');
                hideFlyout('bgcu-flyout');
            }
            if (doctypePicker && !doctypePicker.contains(e.target)) {
                document.getElementById('doctype-dropdown').style.display = 'none';
            }
            if (forwardUnitPicker && !forwardUnitPicker.contains(e.target) &&
                !fwdPauFlyout.contains(e.target) && !fwdBgcuFlyout.contains(e.target)) {
                document.getElementById('forward-unit-dropdown').style.display = 'none';
                hideFlyout('fwd-pau-flyout');
                hideFlyout('fwd-bgcu-flyout');
            }
        });

        // ── Reset upload modal pickers on close
        const uploadBackdrop = document.querySelector('[x-show="open"]');
        if (uploadBackdrop) {
            new MutationObserver(function () {
                if (uploadBackdrop.style.display === 'none') {
                    document.getElementById('unit-hidden-input').value = '';
                    const ul = document.getElementById('unit-picker-label');
                    ul.textContent = 'Select Receiving Unit';
                    ul.style.color = '#6b7280';
                    document.getElementById('unit-dropdown').style.display = 'none';
                    hideFlyout('pau-flyout');
                    hideFlyout('bgcu-flyout');
                    document.getElementById('doctype-hidden-input').value = '';
                    const dl = document.getElementById('doctype-picker-label');
                    dl.textContent = 'Select document type';
                    dl.style.color = '#6b7280';
                    document.getElementById('doctype-dropdown').style.display = 'none';
                }
            }).observe(uploadBackdrop, { attributes: true, attributeFilter: ['style'] });
        }

        // ── Reset forward modal picker on close
        const forwardBackdrop = document.querySelector('[x-show="openForward"]');
        if (forwardBackdrop) {
            new MutationObserver(function () {
                if (forwardBackdrop.style.display === 'none') {
                    if (forwardBackdrop.dataset.submitting === '1') {
                        return;
                    }
                    document.getElementById('forward-unit-hidden-input').value = '';
                    const fl = document.getElementById('forward-unit-picker-label');
                    fl.textContent = 'Select unit to forward to';
                    fl.style.color = '#6b7280';
                    document.getElementById('forward-unit-dropdown').style.display = 'none';
                    hideFlyout('fwd-pau-flyout');
                    hideFlyout('fwd-bgcu-flyout');
                }
            }).observe(forwardBackdrop, { attributes: true, attributeFilter: ['style'] });
        }
    });
</script>

@endsection
