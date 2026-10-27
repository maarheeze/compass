<x-compass::layouts.app :title="__('compass::navigation.sites')">
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <div class="overflow-hidden rounded-md border border-border bg-canvas">
            <table class="w-full text-left text-sm">
                <thead class="bg-canvas-subtle text-xs text-foreground-muted">
                    <tr>
                        <th class="px-4 py-2 font-medium">{{ __('compass::site.site') }}</th>
                        <th class="px-4 py-2 font-medium">{{ __('compass::site.server') }}</th>
                        <th class="px-4 py-2 font-medium">{{ __('compass::site.status') }}</th>
                        <th class="px-4 py-2 font-medium">{{ __('compass::site.client') }}</th>
                        <th class="px-4 py-2 font-medium">{{ __('compass::site.deployed_commit') }}</th>
                        <th class="px-4 py-2 font-medium">{{ __('compass::site.collected_at') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($sites as $site)
                        <tr>
                            <td class="px-4 py-2 font-medium text-foreground">
                                <x-compass::site-link :site="$site" />
                            </td>
                            <td class="px-4 py-2 text-foreground-muted">{{ $site->server_name }}</td>
                            <td class="px-4 py-2">
                                <div class="flex items-center gap-2">
                                    <x-compass::missing-label :missing-at="$site->missing_at" :missing-ignored-at="$site->missing_ignored_at" />
                                    <x-compass::forge.deployment-state-badge :site="$site" />
                                </div>
                            </td>
                            <td class="px-4 py-2">
                                <x-compass::client-status-badge :status="$site->client_status" />
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
                            <td colspan="6" class="px-4 py-6 text-center text-foreground-muted">{{ __('compass::site.no_sites') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</x-compass::layouts.app>
