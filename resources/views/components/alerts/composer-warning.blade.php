<x-compass::alerts.row :url="route(\Maarheeze\Compass\Http\RouteName::GITHUB_REPOSITORY, $alert->repository)" :name="$alert->repository->name">
    <x-slot:icon>
        <x-compass::icons.alert />
    </x-slot:icon>
    <x-slot:text>
        Composer warning
    </x-slot:text>

    <x-compass::composer.warning-badge :repository="$alert->repository" />
</x-compass::alerts.row>
