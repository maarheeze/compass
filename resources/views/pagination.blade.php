@if ($paginator->hasPages())
    <nav aria-label="{{ __('compass::pagination.pagination') }}" class="flex items-center justify-center gap-1 text-sm">
        @if ($paginator->onFirstPage())
            <span class="flex items-center gap-1 rounded-md px-2.5 py-1 text-foreground-muted opacity-50">
                &lsaquo; {{ __('compass::pagination.previous') }}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="flex items-center gap-1 rounded-md border border-transparent px-2.5 py-1 text-accent hover:border-border">
                &lsaquo; {{ __('compass::pagination.previous') }}
            </a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2.5 py-1 text-foreground-muted">{{ $element }}</span>
            @else
                @foreach ($element as $page => $url)
                    @if ($page === $paginator->currentPage())
                        <span aria-current="page" class="min-w-8 rounded-md bg-accent px-2.5 py-1 text-center font-medium text-white">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="min-w-8 rounded-md border border-transparent px-2.5 py-1 text-center text-foreground hover:border-border">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="flex items-center gap-1 rounded-md border border-transparent px-2.5 py-1 text-accent hover:border-border">
                {{ __('compass::pagination.next') }} &rsaquo;
            </a>
        @else
            <span class="flex items-center gap-1 rounded-md px-2.5 py-1 text-foreground-muted opacity-50">
                {{ __('compass::pagination.next') }} &rsaquo;
            </span>
        @endif
    </nav>
@endif
