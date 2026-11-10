@props(['tabs'])

@foreach ($tabs as $tab)
    <x-compass::tab :url="$tab->url" :is-active="$tab->isActive">
        @if ($tab->icon !== null)
            <x-dynamic-component :component="$tab->icon" class="text-foreground-muted" />
        @endif
        {{ $tab->label }}
        @if ($tab->count !== null)
            <span class="rounded-full bg-border/50 px-1.5 text-xs font-medium text-foreground-muted">{{ $tab->count }}</span>
        @endif
    </x-compass::tab>
@endforeach
