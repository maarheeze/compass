<x-compass::layouts.app :title="__('compass::navigation.runs')">
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <section class="space-y-3">
            <x-compass::section-heading>
                <x-slot:icon>
                    <x-compass::icons.sync />
                </x-slot:icon>
                {{ __('compass::run.scheduled_commands') }}
            </x-compass::section-heading>

            <div class="overflow-hidden rounded-md border border-border bg-canvas">
                <table class="w-full table-fixed text-left text-sm">
                    <tbody class="divide-y divide-border">
                        @forelse ($scheduledCommands as $scheduledCommand)
                            <x-compass::scheduled-command-row :scheduled-command="$scheduledCommand" />
                        @empty
                            <tr>
                                <td class="px-4 py-6 text-center text-foreground-muted">{{ __('compass::run.no_scheduled_commands') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="space-y-3">
            <x-compass::section-heading>
                <x-slot:icon>
                    <x-compass::icons.history />
                </x-slot:icon>
                {{ __('compass::run.history') }}
            </x-compass::section-heading>

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
        </section>
    </main>
</x-compass::layouts.app>
