<x-compass::layouts.app :title="__('compass::navigation.dashboard')">
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <div class="grid gap-4 sm:grid-cols-2">
            <x-compass::issue-counts-card :issue-counts="$issueCounts" />
            <x-compass::pull-request-counts-card :pull-request-counts="$pullRequestCounts" />
        </div>
        <section class="space-y-3">
            <x-compass::section-heading>
                <x-slot:icon>
                    <x-compass::icons.alert />
                </x-slot:icon>
                {{ __('compass::alert.alerts') }}
            </x-compass::section-heading>
            <div class="overflow-hidden rounded-md border border-border bg-canvas">
                <table class="w-full text-left text-sm">
                    <thead class="bg-canvas-subtle text-xs text-foreground-muted">
                        <tr>
                            <th class="px-4 py-2 font-medium">{{ __('compass::alert.alert') }}</th>
                            <th class="px-4 py-2 font-medium">{{ __('compass::alert.description') }}</th>
                            <th class="px-4 py-2 font-medium">{{ __('compass::alert.open_since') }}</th>
                            <th class="px-4 py-2 font-medium">{{ __('compass::alert.condition') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($alerts as $alert)
                            <x-compass::alert-row :alert="$alert" />
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-foreground-muted">{{ __('compass::alert.no_alerts') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</x-compass::layouts.app>
