<x-compass::alerts.row :url="route(\Maarheeze\Compass\Http\RouteName::GITHUB_REPOSITORY, $alert->repository)" :name="$alert->repository->name">
    <x-slot:icon>
        <x-compass::icons.repo />
    </x-slot:icon>
    <x-slot:text>
        missing since
        <x-compass::time :time="$alert->repository->missing_at" />
    </x-slot:text>
</x-compass::alerts.row>
