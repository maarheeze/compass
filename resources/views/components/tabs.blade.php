@props(['tabs'])

@foreach ($tabs as $tab)
    <x-compass::tab :url="$tab->url" :is-active="$tab->isActive">
        <x-dynamic-component :component="$tab->icon" class="text-foreground-muted" />
        {{ $tab->label }}
    </x-compass::tab>
@endforeach
