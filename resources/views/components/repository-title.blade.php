@props(['repository'])

<x-compass::subject-name :subject="$repository">
    {{ $slot }}
    <x-compass::badge type="neutral">
        <x-compass::enum-label :labelled-enum="$repository->visibility" />
    </x-compass::badge>
</x-compass::subject-name>
