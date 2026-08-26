@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" style="display: flex; align-items: center; gap: 4px;">
        <span style="font-size: 11px; color: var(--text-muted); margin-right: 6px; font-weight: 700;">
            <span data-i18n="page_indicator">Страница</span> {{ $paginator->currentPage() }} <span data-i18n="of_indicator">из</span> {{ $paginator->lastPage() }} ({{ $paginator->total() }} <span data-i18n="items_indicator">карт</span>)
        </span>

        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="pagination-btn disabled" aria-disabled="true" aria-label="@lang('pagination.previous')" data-i18n="prev_page">
                &lsaquo; Назад
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pagination-btn" aria-label="@lang('pagination.previous')" data-i18n="prev_page">
                &lsaquo; Назад
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="pagination-btn disabled" aria-disabled="true">{{ $element }}</span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="pagination-btn active" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="pagination-btn">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pagination-btn" aria-label="@lang('pagination.next')" data-i18n="next_page">
                Вперед &rsaquo;
            </a>
        @else
            <span class="pagination-btn disabled" aria-disabled="true" aria-label="@lang('pagination.next')" data-i18n="next_page">
                Вперед &rsaquo;
            </span>
        @endif
    </nav>
@endif
