@props(['tabs'])

@foreach ($tabs as $tab)
    <x-compass::tab :url="$tab->url" :is-active="$tab->isActive">
        @if ($tab->icon !== null)
            <x-dynamic-component :component="$tab->icon" class="text-foreground-muted" />
        @endif
        {{ __($tab->labelKey) }}
    </x-compass::tab>
@endforeach
