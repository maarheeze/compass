@props(['repositories'])

<x-compass::list>
    <x-slot:icon>
        <x-compass::icons.repo />
    </x-slot:icon>
    <x-slot:title>{{ __('compass::navigation.repositories') }}</x-slot:title>
    <x-slot:empty>{{ __('compass::repository.none') }}</x-slot:empty>
    <x-slot:rows>
        @foreach ($repositories as $repository)
            <x-compass::repository-row :repository="$repository" />
        @endforeach
    </x-slot:rows>
</x-compass::list>
