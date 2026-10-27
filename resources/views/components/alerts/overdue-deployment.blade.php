<x-compass::alerts.row>
    <x-slot:link>
        <x-compass::site-link :site="$alert->site" class="font-medium" />
    </x-slot:link>
    <x-slot:icon>
        <x-compass::icons.server />
    </x-slot:icon>
    <x-slot:text>
        Auto-deploy overdue
    </x-slot:text>

    <x-compass::commit-hash :repository="$alert->site->repository" :commit-hash="$alert->site->deployed_commit_hash" />
    <x-compass::comparison-link :repository="$alert->site->repository" :base-commit-hash="$alert->site->deployed_commit_hash" />
    <x-compass::forge.link :site="$alert->site" />
</x-compass::alerts.row>
