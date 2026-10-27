<x-compass::layouts.app :title="$site->name">
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <div class="space-y-1">
            <h1 class="text-lg font-semibold text-foreground">
                <x-compass::site-link :site="$site" />
                <span class="font-mono">{{ $endpointPath->path }}</span>
            </h1>
        </div>

        <div class="overflow-hidden rounded-md border border-border bg-canvas">
            <table class="w-full text-left text-sm">
                <thead class="bg-canvas-subtle text-xs text-foreground-muted">
                    <tr>
                        <th class="px-4 py-2 font-medium">Checked at</th>
                        <th class="px-4 py-2 font-medium">URL</th>
                        <th class="px-4 py-2 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($siteEndpointChecks as $siteEndpointCheck)
                        <tr>
                            <td class="px-4 py-2 text-foreground-muted">
                                <x-compass::time :time="$siteEndpointCheck->created_at" />
                            </td>
                            <td class="px-4 py-2 font-mono text-foreground-muted">{{ $siteEndpointCheck->url }}</td>
                            <td class="px-4 py-2 text-foreground">
                                @if ($siteEndpointCheck->status_code === null)
                                    Unreachable
                                @else
                                    {{ $siteEndpointCheck->status_code }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-foreground-muted">No checks yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $siteEndpointChecks->links() }}
    </main>
</x-compass::layouts.app>
