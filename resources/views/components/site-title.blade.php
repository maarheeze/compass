@props(['site'])

<x-compass::subject-name :subject="$site">
    {{ $slot }}
    <x-compass::deployment-state-badge :site="$site" />
    <x-compass::site-maintenance-mode-badge :site="$site" />
</x-compass::subject-name>
