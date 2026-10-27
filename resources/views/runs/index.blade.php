<x-compass::layouts.app :title="__('compass::navigation.runs')">
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <nav class="flex items-center gap-6 border-b border-border text-sm font-medium">
            <x-compass::run-tabs />
        </nav>

        <div class="overflow-hidden rounded-md border border-border bg-canvas">
            <table class="w-full text-left text-sm">
                <thead class="bg-canvas-subtle text-xs text-foreground-muted">
                    <tr>
                        <th class="px-4 py-2 font-medium">{{ __('compass::run.run') }}</th>
                        <th class="px-4 py-2 font-medium">{{ __('compass::run.status') }}</th>
                        <th class="px-4 py-2 font-medium">{{ __('compass::run.started') }}</th>
                        <th class="px-4 py-2 font-medium">{{ __('compass::run.ended') }}</th>
                        <th class="px-4 py-2 font-medium">{{ __('compass::run.failed_jobs') }}</th>
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
                            <td colspan="5" class="px-4 py-6 text-center text-foreground-muted">{{ __('compass::run.no_runs') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $runs->links() }}
    </main>
</x-compass::layouts.app>
