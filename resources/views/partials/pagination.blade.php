@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between">
        <div class="flex justify-between flex-1 sm:hidden">
            {{-- Mobile Links --}}
            @if ($paginator->onFirstPage())
                <span
                    class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-300 bg-white border border-slate-100 cursor-default rounded-xl">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}"
                    class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-100 rounded-xl hover:bg-slate-50 transition-all">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                    class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-100 rounded-xl hover:bg-slate-50 transition-all">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span
                    class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-300 bg-white border border-slate-100 cursor-default rounded-xl">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                    Showing <span class="text-slate-900">{{ $paginator->firstItem() }}</span> to <span
                        class="text-slate-900">{{ $paginator->lastItem() }}</span> of <span
                        class="text-slate-900">{{ $paginator->total() }}</span>
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex shadow-sm rounded-2xl overflow-hidden">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span
                                class="relative inline-flex items-center px-3 py-2 bg-white border border-slate-100 text-slate-200 cursor-default"
                                aria-hidden="true">
                                <i data-lucide="chevron-left" class="w-5 h-5"></i>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                            class="relative inline-flex items-center px-3 py-2 bg-white border border-slate-100 text-slate-500 hover:text-(--brand) transition-colors"
                            aria-label="{{ __('pagination.previous') }}">
                            <i data-lucide="chevron-left" class="w-5 h-5"></i>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true"
                                class="relative inline-flex items-center px-4 py-2 bg-white border border-slate-100 text-slate-700 cursor-default font-bold text-sm">{{ $element }}</span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span
                                            class="relative inline-flex items-center px-4 py-2 bg-(--brand) border border-(--brand) text-white text-sm font-black transition-all">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}"
                                        class="relative inline-flex items-center px-4 py-2 bg-white border border-slate-100 text-slate-500 hover:bg-slate-50 hover:text-(--brand) font-bold text-sm transition-all"
                                        aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                            class="relative inline-flex items-center px-3 py-2 bg-white border border-slate-100 text-slate-500 hover:text-(--brand) transition-colors"
                            aria-label="{{ __('pagination.next') }}">
                            <i data-lucide="chevron-right" class="w-5 h-5"></i>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span
                                class="relative inline-flex items-center px-3 py-2 bg-white border border-slate-100 text-slate-200 cursor-default"
                                aria-hidden="true">
                                <i data-lucide="chevron-right" class="w-5 h-5"></i>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
