<x-compass::alerts.row>
    <x-slot:link>
        <x-compass::repository-link :repository="$alert->repository" class="font-medium" />
    </x-slot:link>
    <x-slot:icon>
        <x-compass::icons.alert />
    </x-slot:icon>
    <x-slot:text>
        {{ __('compass::alert.composer_warning') }}
    </x-slot:text>

    <x-compass::composer.warning-badge :repository="$alert->repository" />
</x-compass::alerts.row>
