<x-compass::alerts.row :url="route(\Maarheeze\Compass\Http\RouteName::FORGE_SITE, $alert->site)" :name="$alert->site->name">
    <x-slot:icon>
        <x-compass::icons.server />
    </x-slot:icon>
    <x-slot:text>
        Behind on deployment
    </x-slot:text>

    <x-compass::commit-hash :repository="$alert->site->repository" :commit-hash="$alert->site->deployed_commit_hash" />
    <x-compass::comparison-link :repository="$alert->site->repository" :base-commit-hash="$alert->site->deployed_commit_hash" />
    <x-compass::forge.link :site="$alert->site" />
</x-compass::alerts.row>
