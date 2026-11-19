<x-compass::sidebar-section :title="$filter->title">
    <ul class="-mx-2 space-y-1">
        @foreach ($filter->options as $filterOption)
            <li>
                <a
                    href="{{ $optionUrl($filterOption) }}"
                    @class([
                        'flex items-center gap-2 rounded-md px-2 py-1 text-foreground hover:bg-accent-muted',
                        'bg-accent-muted font-semibold' => $isSelected($filterOption),
                    ])>
                    @if ($filterOption->avatarUrl !== null)
                        <x-compass::avatar-image :name="$filterOption->label" :image-url="$filterOption->avatarUrl" class="size-5" />
                    @endif
                    <span class="min-w-0 break-words">{{ $filterOption->label }}</span>
                    <span class="ml-auto text-xs text-foreground-muted">{{ $filterOption->count }}</span>
                </a>
            </li>
        @endforeach
    </ul>
    @if ($isFiltered)
        <a href="{{ $unfilteredUrl }}" class="text-accent hover:underline">{{ __('compass::filter.clear') }}</a>
    @endif
</x-compass::sidebar-section>
