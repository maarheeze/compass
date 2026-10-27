<x-compass::alerts.row>
    <x-slot:link>
        <x-compass::repository-link :repository="$alert->repository" class="font-medium" />
    </x-slot:link>
    <x-slot:icon>
        <x-compass::icons.repo />
    </x-slot:icon>
    <x-slot:text>
        {{ __('compass::alert.missing_since') }}
        <x-compass::time :time="$alert->repository->missing_at" />
    </x-slot:text>
</x-compass::alerts.row>
