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
            <x-compass::alert-table :alerts="$alerts" />
        </section>
    </main>
</x-compass::layouts.app>
