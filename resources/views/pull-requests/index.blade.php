<x-compass::layouts.app :title="__('compass::navigation.pull_requests')">
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <nav class="flex items-center gap-6 border-b border-border text-sm font-medium">
            <x-compass::pull-request-tabs />
        </nav>

        <div class="overflow-hidden rounded-md border border-border bg-canvas">
            <table class="w-full table-fixed text-left text-sm">
                <tbody class="divide-y divide-border">
                    @forelse ($pullRequests as $pullRequest)
                        <x-compass::pull-request-row :pull-request="$pullRequest" />
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-6 text-center text-foreground-muted">{{ __('compass::pull-request.no_open_pull_requests') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</x-compass::layouts.app>
