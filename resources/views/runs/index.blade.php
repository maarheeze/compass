<x-compass::layouts.app>
    <x-slot:title>{{ __('compass::navigation.runs') }}</x-slot:title>
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl px-4 py-6 md:px-6 lg:px-8">
        <x-compass::page-columns>
            <div data-compass-live-region="runs" class="space-y-3">
                <x-compass::list>
                    <x-slot:icon>
                        <x-compass::icons.history />
                    </x-slot:icon>
                    <x-slot:title>{{ __('compass::run.history') }}</x-slot:title>
                    <x-slot:empty>{{ __('compass::run.none') }}</x-slot:empty>
                    <x-slot:rows>
                        @foreach ($runs as $run)
                            <x-compass::run-row :run="$run" />
                        @endforeach
                    </x-slot:rows>
                </x-compass::list>

                {{ $runs->links('compass::pagination') }}
            </div>

            <x-slot:aside>
                <x-compass::sidebar-section :title="__('compass::run.scheduled_commands')">
                    @if ($scheduledCommands->isEmpty())
                        <p class="text-foreground-muted">{{ __('compass::run.no_scheduled_commands') }}</p>
                    @else
                        <ul class="space-y-3">
                            @foreach ($scheduledCommands as $scheduledCommand)
                                <x-compass::scheduled-command-row :scheduled-command="$scheduledCommand" />
                            @endforeach
                        </ul>
                    @endif
                </x-compass::sidebar-section>
            </x-slot:aside>
        </x-compass::page-columns>
    </main>
</x-compass::layouts.app>
