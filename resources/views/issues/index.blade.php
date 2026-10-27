<x-compass::layouts.app :title="__('Issues')">
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <nav class="flex items-center gap-6 border-b border-border text-sm font-medium">
            <x-compass::issue-tabs />
        </nav>

        <div class="overflow-hidden rounded-md border border-border bg-canvas">
            <table class="w-full table-fixed text-left text-sm">
                <thead class="bg-canvas-subtle text-xs text-foreground-muted">
                    <tr>
                        <th class="w-48 px-4 py-2 font-medium">Repository</th>
                        <x-compass::issue-heading-cells />
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($issues as $issue)
                        <tr>
                            <td class="truncate px-4 py-2 text-foreground-muted">
                                <x-compass::repository-link :repository="$issue->repository" />
                            </td>
                            <x-compass::issue-cells :issue="$issue" />
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-foreground-muted">No open issues.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</x-compass::layouts.app>
