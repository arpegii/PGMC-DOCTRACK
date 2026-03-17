@extends('layouts.app')

@section('header')
<div class="page-hero">
    <div>
        <h1 class="page-title">Outgoing Documents</h1>
        <p class="page-subtitle">Documents sent by your unit and current disposition</p>
    </div>
</div>
@endsection

@section('content')

<!-- Success Message -->
@if (session('success'))
    <div class="py-4">
        <div class="panel-surface-soft border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    </div>
@endif

<div class="pb-2">
    <form method="GET" action="{{ route('outgoing.index') }}" class="filter-card px-4 py-4 md:px-5 border border-[#d8e2f0] bg-gradient-to-b from-white to-[#f8fbff]" data-live-search-form>
        <div class="flex flex-col gap-3 md:flex-row md:items-end">
            <div class="min-w-0 md:w-1/3">
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.06em] text-slate-500">Filter by unit</label>
                @php
                    $selectedUnitLabel = 'All units';
                    $pauSubUnits = ['Resumption NCO', 'TOP NCO', 'Restoration NCO', 'Prior Years NCO', 'Pension Differential 18-19', 'Own Right NCO'];
                    $bgcuSubUnits = ['Posthumous NCO', 'Retirement NCO', 'RSAB NCO', 'CDD NCO'];
                    foreach ($filterUnits as $unit) {
                        if ((string) $unit->id === (string) $selectedUnitId) {
                            $selectedUnitLabel = $unit->name;
                            break;
                        }
                    }
                @endphp
                <div class="unit-filter" data-filter-unit-picker>
                    <input type="hidden" name="unit_id" value="{{ $selectedUnitId ?? '' }}" data-filter-unit-input>
                    <button
                        type="button"
                        class="unit-filter-trigger"
                        data-filter-unit-trigger
                        aria-haspopup="listbox"
                        aria-expanded="false"
                        style="color: {{ $selectedUnitId ? '#111827' : '#6b7280' }};"
                    >
                        <span class="unit-filter-label" data-filter-unit-label>{{ $selectedUnitLabel }}</span>
                        <svg class="unit-filter-chevron" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M5 7l5 5 5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <div class="unit-filter-menu is-hidden" data-filter-unit-menu role="listbox">
                        <div
                            class="unit-filter-option"
                            data-filter-unit-option
                            data-unit-id=""
                            data-unit-name="All units"
                        >
                            All units
                        </div>
                        @foreach($filterUnits as $unit)
                            @if(in_array($unit->name, array_merge($pauSubUnits, $bgcuSubUnits), true))
                                @continue
                            @endif
                            @if($unit->name === 'PAU')
                                <div
                                    class="unit-filter-option"
                                    data-filter-unit-option
                                    data-unit-id="{{ $unit->id }}"
                                    data-unit-name="PAU"
                                    data-has-flyout="pau"
                                >
                                    <span>PAU</span>
                                    <svg class="unit-filter-option-icon" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                        <path d="M8 5l5 5-5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            @elseif($unit->name === 'BGCU')
                                <div
                                    class="unit-filter-option"
                                    data-filter-unit-option
                                    data-unit-id="{{ $unit->id }}"
                                    data-unit-name="BGCU"
                                    data-has-flyout="bgcu"
                                >
                                    <span>BGCU</span>
                                    <svg class="unit-filter-option-icon" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                        <path d="M8 5l5 5-5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            @else
                                <div
                                    class="unit-filter-option"
                                    data-filter-unit-option
                                    data-unit-id="{{ $unit->id }}"
                                    data-unit-name="{{ $unit->name }}"
                                >
                                    {{ $unit->name }}
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
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

<!-- Filter flyouts -->
<div
    data-filter-flyout="pau"
    style="
        display:none;
        position:fixed;
        width:230px;
        background:white;
        border:1px solid #c7dcff;
        border-radius:0.625rem;
        box-shadow:0 8px 24px rgba(0,0,0,0.15);
        z-index:999999;
        overflow:hidden;
    "
>
    <div style="padding:0.5rem 1rem 0.4rem; font-size:0.7rem; font-weight:700; color:#1e5ba8; background:#f0f6ff; border-bottom:1px solid #c7dcff; letter-spacing:0.05em;">
        PAU SUB-UNITS
    </div>
    @foreach($filterUnits as $subUnit)
        @if(in_array($subUnit->name, $pauSubUnits, true))
            <div
                class="unit-filter-flyout-item"
                data-filter-flyout-item
                data-unit-id="{{ $subUnit->id }}"
                data-unit-name="{{ $subUnit->name }}"
            >
                {{ $subUnit->name }}
            </div>
        @endif
    @endforeach
</div>

<div
    data-filter-flyout="bgcu"
    style="
        display:none;
        position:fixed;
        width:210px;
        background:white;
        border:1px solid #c7dcff;
        border-radius:0.625rem;
        box-shadow:0 8px 24px rgba(0,0,0,0.15);
        z-index:999999;
        overflow:hidden;
    "
>
    <div style="padding:0.5rem 1rem 0.4rem; font-size:0.7rem; font-weight:700; color:#1e5ba8; background:#f0f6ff; border-bottom:1px solid #c7dcff; letter-spacing:0.05em;">
        BGCU SUB-UNITS
    </div>
    @foreach($filterUnits as $subUnit)
        @if(in_array($subUnit->name, $bgcuSubUnits, true))
            <div
                class="unit-filter-flyout-item"
                data-filter-flyout-item
                data-unit-id="{{ $subUnit->id }}"
                data-unit-name="{{ $subUnit->name }}"
            >
                {{ $subUnit->name }}
            </div>
        @endif
    @endforeach
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
                                <span class="badge-chip bg-blue-50 text-blue-700">
                                    {{ $document->document_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                {{ $document->receivingUnit->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($document->status == 'incoming')
                                    <span class="badge-chip bg-amber-50 text-amber-700">Pending</span>
                                @elseif($document->status == 'received')
                                    <span class="badge-chip bg-emerald-50 text-emerald-700">Received</span>
                                @elseif($document->status == 'rejected')
                                    <span class="badge-chip bg-red-50 text-red-700">Rejected</span>
                                @else
                                    <span class="badge-chip bg-slate-100 text-slate-700">{{ ucfirst($document->status) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-center">
                                {{ $document->created_at->format('M d, Y h:i A') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2 table-actions">
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
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="table-empty">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="text-lg">📄</span>
                                    <span>No outgoing documents found</span>
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

<!-- PAU Flyout -->
<div id="pau-flyout" style="
    display:none;
    position:fixed;
    width:230px;
    background:white;
    border:1px solid #c7dcff;
    border-radius:0.625rem;
    box-shadow:0 8px 24px rgba(0,0,0,0.15);
    z-index:999999;
    overflow:hidden;
">
    <div style="padding:0.5rem 1rem 0.4rem; font-size:0.7rem; font-weight:700; color:#1e5ba8; background:#f0f6ff; border-bottom:1px solid #c7dcff; letter-spacing:0.05em;">
        PAU SUB-UNITS
    </div>
    @foreach($units as $subUnit)
        @if(in_array($subUnit->name, [
            'Resumption NCO', 'TOP NCO', 'Restoration NCO',
            'Prior Years NCO', 'Pension Differential 18-19', 'Own Right NCO'
        ]))
            <div
                class="flyout-item"
                data-unit-id="{{ $subUnit->id }}"
                data-unit-name="{{ $subUnit->name }}"
                style="padding:0.6rem 1rem; font-size:0.875rem; color:#374151; cursor:pointer; transition:background 0.15s;"
            >
                {{ $subUnit->name }}
            </div>
        @endif
    @endforeach
</div>

<!-- BGCU Flyout -->
<div id="bgcu-flyout" style="
    display:none;
    position:fixed;
    width:210px;
    background:white;
    border:1px solid #c7dcff;
    border-radius:0.625rem;
    box-shadow:0 8px 24px rgba(0,0,0,0.15);
    z-index:999999;
    overflow:hidden;
">
    <div style="padding:0.5rem 1rem 0.4rem; font-size:0.7rem; font-weight:700; color:#1e5ba8; background:#f0f6ff; border-bottom:1px solid #c7dcff; letter-spacing:0.05em;">
        BGCU SUB-UNITS
    </div>
    @foreach($units as $subUnit)
        @if(in_array($subUnit->name, [
            'Posthumous NCO', 'Retirement NCO', 'RSAB NCO', 'CDD NCO'
        ]))
            <div
                class="flyout-item"
                data-unit-id="{{ $subUnit->id }}"
                data-unit-name="{{ $subUnit->name }}"
                style="padding:0.6rem 1rem; font-size:0.875rem; color:#374151; cursor:pointer; transition:background 0.15s;"
            >
                {{ $subUnit->name }}
            </div>
        @endif
    @endforeach
</div>

<style>
    [x-cloak] {
        display: none !important;
    }
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

    // ── Unit picker ──────────────────────────────────────────────────────────
    function toggleUnitDropdown(e) {
        e.stopPropagation();
        const dropdown = document.getElementById('unit-dropdown');
        const isOpen   = dropdown.style.display === 'block';
        dropdown.style.display = isOpen ? 'none' : 'block';
        if (isOpen) {
            hideFlyout('pau-flyout');
            hideFlyout('bgcu-flyout');
        }
        // Close doctype if open
        document.getElementById('doctype-dropdown').style.display = 'none';
    }

    function selectUnit(id, name) {
        document.getElementById('unit-hidden-input').value = id;
        const label       = document.getElementById('unit-picker-label');
        label.textContent = name;
        label.style.color = '#111827';
        document.getElementById('unit-dropdown').style.display = 'none';
        hideFlyout('pau-flyout');
        hideFlyout('bgcu-flyout');
    }

    function hideFlyout(id) {
        clearTimeout(flyoutTimers[id]);
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    }

    // ── Doctype picker ───────────────────────────────────────────────────────
    function toggleDoctypeDropdown(e) {
        e.stopPropagation();
        const dropdown = document.getElementById('doctype-dropdown');
        const isOpen   = dropdown.style.display === 'block';
        dropdown.style.display = isOpen ? 'none' : 'block';
        // Close unit dropdown if open
        document.getElementById('unit-dropdown').style.display = 'none';
        hideFlyout('pau-flyout');
        hideFlyout('bgcu-flyout');
    }

    function selectDoctype(value) {
        document.getElementById('doctype-hidden-input').value = value;
        const label       = document.getElementById('doctype-picker-label');
        label.textContent = value;
        label.style.color = '#111827';
        document.getElementById('doctype-dropdown').style.display = 'none';
    }

    // ── Init on DOM ready ────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {

        // Move flyouts to <body> so modal overflow never clips them
        const pauFlyout  = document.getElementById('pau-flyout');
        const bgcuFlyout = document.getElementById('bgcu-flyout');
        document.body.appendChild(pauFlyout);
        document.body.appendChild(bgcuFlyout);

        // Flyout item hover + click
        document.querySelectorAll('#pau-flyout .flyout-item, #bgcu-flyout .flyout-item').forEach(item => {
            item.addEventListener('mouseenter', () => item.style.background = '#eff6ff');
            item.addEventListener('mouseleave', () => item.style.background = '');
            item.addEventListener('click', () => selectUnit(item.dataset.unitId, item.dataset.unitName));
        });

        // Keep flyout open when mouse is inside it
        [pauFlyout, bgcuFlyout].forEach(flyout => {
            flyout.addEventListener('mouseenter', () => clearTimeout(flyoutTimers[flyout.id]));
            flyout.addEventListener('mouseleave', () => hideFlyout(flyout.id));
        });

        // Unit row hover + click
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
                    flyoutTimers[flyoutKey + '-flyout'] = setTimeout(() => {
                        hideFlyout(flyoutKey + '-flyout');
                    }, 120);
                }
            });
row.addEventListener('click', () => {
    selectUnit(row.dataset.unitId, row.dataset.unitName);
});
        });

        // Doctype row hover + click
        document.querySelectorAll('.doctype-row').forEach(row => {
            row.addEventListener('mouseenter', () => row.style.background = '#f3f4f6');
            row.addEventListener('mouseleave', () => row.style.background = '');
            row.addEventListener('click',      () => selectDoctype(row.dataset.value));
        });

        // Close all dropdowns when clicking outside
        document.addEventListener('click', function (e) {
            const unitPicker    = document.getElementById('unit-picker');
            const doctypePicker = document.getElementById('doctype-picker');

            if (unitPicker && !unitPicker.contains(e.target) &&
                !pauFlyout.contains(e.target) && !bgcuFlyout.contains(e.target)) {
                document.getElementById('unit-dropdown').style.display = 'none';
                hideFlyout('pau-flyout');
                hideFlyout('bgcu-flyout');
            }
            if (doctypePicker && !doctypePicker.contains(e.target)) {
                document.getElementById('doctype-dropdown').style.display = 'none';
            }
        });

        // Reset both pickers when modal closes
        const modalBackdrop = document.querySelector('[x-show="open"]');
        if (modalBackdrop) {
            new MutationObserver(function () {
                if (modalBackdrop.style.display === 'none') {
                    // Unit picker
                    document.getElementById('unit-hidden-input').value = '';
                    const unitLabel = document.getElementById('unit-picker-label');
                    unitLabel.textContent = 'Select Receiving Unit';
                    unitLabel.style.color = '#6b7280';
                    document.getElementById('unit-dropdown').style.display = 'none';
                    hideFlyout('pau-flyout');
                    hideFlyout('bgcu-flyout');
                    // Doctype picker
                    document.getElementById('doctype-hidden-input').value = '';
                    const doctypeLabel = document.getElementById('doctype-picker-label');
                    doctypeLabel.textContent = 'Select document type';
                    doctypeLabel.style.color = '#6b7280';
                    document.getElementById('doctype-dropdown').style.display = 'none';
                }
            }).observe(modalBackdrop, { attributes: true, attributeFilter: ['style'] });
        }
    });
</script>

@endsection
