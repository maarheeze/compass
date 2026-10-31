<x-compass::layouts.app :title="__('compass::navigation.runs')">
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <nav class="flex items-center gap-6 border-b border-border text-sm font-medium">
            <x-compass::run-tabs />
        </nav>

        <div class="overflow-hidden rounded-md border border-border bg-canvas">
            <table class="w-full table-fixed text-left text-sm">
                <tbody class="divide-y divide-border">
                    @forelse ($runs as $run)
                        <x-compass::run-row :run="$run" />
                    @empty
                        <tr>
                            <td class="px-4 py-6 text-center text-foreground-muted">{{ __('compass::run.no_runs') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $runs->links('compass::pagination') }}
    </main>
</x-compass::layouts.app>
