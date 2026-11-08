@props(['tabs'])

@foreach ($tabs as $tab)
    <x-compass::tab :url="$tab->url" :is-active="$tab->isActive">
        @if ($tab->icon !== null)
            <x-dynamic-component :component="$tab->icon" class="text-foreground-muted" />
        @endif
        {{ $tab->label }}
    </x-compass::tab>
@endforeach
