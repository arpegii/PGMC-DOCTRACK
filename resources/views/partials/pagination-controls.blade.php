<div class="flex items-center justify-between gap-3 border-t border-slate-200 bg-slate-50 px-4 py-3 md:px-6">
    @if($paginator->onFirstPage())
        <span class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-400">
            Previous
        </span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}"
           class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
            Previous
        </a>
    @endif

    <span class="text-sm font-medium text-slate-600">
        Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
    </span>

    @if($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}"
           class="inline-flex items-center justify-center rounded-lg border border-blue-200 bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
            Next
        </a>
    @else
        <span class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-400">
            Next
        </span>
    @endif
</div>
