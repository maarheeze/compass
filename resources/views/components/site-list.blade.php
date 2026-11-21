@props(['sites'])

<x-compass::list>
    <x-slot:icon>
        <x-compass::icons.server />
    </x-slot:icon>
    <x-slot:title>{{ __('compass::navigation.sites') }}</x-slot:title>
    <x-slot:empty>{{ __('compass::site.none') }}</x-slot:empty>
    <x-slot:rows>
        @foreach ($sites as $site)
            <x-compass::site-row :site="$site" />
        @endforeach
    </x-slot:rows>
</x-compass::list>
