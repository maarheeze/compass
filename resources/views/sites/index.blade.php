<x-compass::layouts.app :title="__('Sites')">
    <x-compass::navigation :section="\Maarheeze\Compass\View\NavigationSection::SITES" />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <div class="overflow-hidden rounded-md border border-border bg-canvas">
            <table class="w-full text-left text-sm">
                <thead class="bg-canvas-subtle text-xs text-foreground-muted">
                    <tr>
                        <th class="px-4 py-2 font-medium">Site</th>
                        <th class="px-4 py-2 font-medium">Server</th>
                        <th class="px-4 py-2 font-medium">Status</th>
                        <th class="px-4 py-2 font-medium">Deployed commit</th>
                        <th class="px-4 py-2 font-medium">Collected at</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($sites as $site)
                        <tr>
                            <td class="px-4 py-2 font-medium text-foreground">
                                <a href="{{ route(\Maarheeze\Compass\Http\RouteName::FORGE_SITE, $site) }}" class="text-accent hover:underline">{{ $site->name }}</a>
                            </td>
                            <td class="px-4 py-2 text-foreground-muted">{{ $site->server_name }}</td>
                            <td class="px-4 py-2">
                                <x-compass::forge.deployment-state-label :site="$site" />
                            </td>
                            <td class="px-4 py-2 text-foreground-muted">
                                <x-compass::commit-hash :repository="$site->repository" :commit-hash="$site->deployed_commit_hash" />
                            </td>
                            <td class="px-4 py-2 text-foreground-muted">
                                <x-compass::time :time="$site->collected_at" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-foreground-muted">No sites collected yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</x-compass::layouts.app>
