<x-compass::layouts.app :title="__('Runs')">
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <nav class="flex items-center gap-6 border-b border-border text-sm font-medium">
            <x-compass::tab :url="route(\Maarheeze\Compass\Http\RouteName::RUNS)" :is-active="$activeRunType === null">
                All runs
            </x-compass::tab>
            <x-compass::tab
                :url="route(\Maarheeze\Compass\Http\RouteName::RUNS_OF_TYPE, \Maarheeze\Compass\Collector\RunType::GITHUB_SYNC)"
                :is-active="$activeRunType === \Maarheeze\Compass\Collector\RunType::GITHUB_SYNC">
                <x-compass::run.type-name :type="\Maarheeze\Compass\Collector\RunType::GITHUB_SYNC" />
            </x-compass::tab>
            <x-compass::tab
                :url="route(\Maarheeze\Compass\Http\RouteName::RUNS_OF_TYPE, \Maarheeze\Compass\Collector\RunType::FORGE_SYNC)"
                :is-active="$activeRunType === \Maarheeze\Compass\Collector\RunType::FORGE_SYNC">
                <x-compass::run.type-name :type="\Maarheeze\Compass\Collector\RunType::FORGE_SYNC" />
            </x-compass::tab>
            <x-compass::tab
                :url="route(\Maarheeze\Compass\Http\RouteName::RUNS_OF_TYPE, \Maarheeze\Compass\Collector\RunType::ENDPOINT_CHECK)"
                :is-active="$activeRunType === \Maarheeze\Compass\Collector\RunType::ENDPOINT_CHECK">
                <x-compass::run.type-name :type="\Maarheeze\Compass\Collector\RunType::ENDPOINT_CHECK" />
            </x-compass::tab>
        </nav>

        <div class="overflow-hidden rounded-md border border-border bg-canvas">
            <table class="w-full text-left text-sm">
                <thead class="bg-canvas-subtle text-xs text-foreground-muted">
                    <tr>
                        <th class="px-4 py-2 font-medium">Run</th>
                        <th class="px-4 py-2 font-medium">Status</th>
                        <th class="px-4 py-2 font-medium">Started</th>
                        <th class="px-4 py-2 font-medium">Ended</th>
                        <th class="px-4 py-2 font-medium">Failed jobs</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($runs as $run)
                        <tr>
                            <td class="px-4 py-2 text-foreground">
                                <x-compass::run.type-name :type="$run->type" />
                                <span class="block font-mono text-xs text-foreground-muted">{{ $run->id }}</span>
                            </td>
                            <td class="px-4 py-2">
                                <x-compass::run.status-label :status="$run->status" />
                            </td>
                            <td class="px-4 py-2 text-foreground-muted">
                                <x-compass::date-time :time="$run->created_at" />
                            </td>
                            <td class="px-4 py-2 text-foreground-muted">
                                @if ($run->finished_at !== null)
                                    <x-compass::date-time :time="$run->finished_at" />
                                @endif
                            </td>
                            <td class="px-4 py-2 text-foreground-muted">{{ $run->failed_job_count }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-foreground-muted">No runs yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $runs->links() }}
    </main>
</x-compass::layouts.app>
