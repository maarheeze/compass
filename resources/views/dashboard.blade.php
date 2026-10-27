<x-compass::layouts.app :title="__('compass::navigation.dashboard')">
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <div class="grid gap-4 sm:grid-cols-2">
            <x-compass::issue-counts-card :issue-counts="$issueCounts" />
            <x-compass::pull-request-counts-card :pull-request-counts="$pullRequestCounts" />
        </div>

        <section class="overflow-hidden rounded-md border border-border bg-canvas">
            <div class="px-4 py-3">
                <x-compass::section-heading>
                    <x-slot:icon>
                        <x-compass::icons.alert />
                    </x-slot:icon>
                    {{ __('compass::dashboard.alerts') }}
                </x-compass::section-heading>
            </div>

            @if ($alerts->isEmpty())
                <p class="flex items-center gap-2 border-t border-border px-4 py-3 text-sm text-emerald-700 dark:text-emerald-400">
                    <x-compass::icons.check-circle />
                    {{ __('compass::dashboard.no_alerts') }}
                </p>
            @else
                <ul class="divide-y divide-border border-t border-border">
                    @foreach ($alerts as $alert)
                        <x-compass::alert-item :alert="$alert" />
                    @endforeach
                </ul>
            @endif
        </section>
    </main>
</x-compass::layouts.app>
