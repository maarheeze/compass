<x-compass::layouts.app :title="__('Dashboard')">
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <div class="grid gap-4 sm:grid-cols-2">
            <x-compass::assigned-and-open-counts
                :title="__('Issues')"
                :assigned-route-name="\Maarheeze\Compass\Http\RouteName::GITHUB_ISSUES_ASSIGNED"
                :assigned-count="$issueCounts->assignedIssueCount"
                :open-route-name="\Maarheeze\Compass\Http\RouteName::GITHUB_ISSUES"
                :open-count="$issueCounts->openIssueCount">
                <x-slot:icon>
                    <x-compass::icons.issue-opened />
                </x-slot:icon>
            </x-compass::assigned-and-open-counts>
            <x-compass::assigned-and-open-counts
                :title="__('Pull requests')"
                :assigned-route-name="\Maarheeze\Compass\Http\RouteName::GITHUB_PULL_REQUESTS_ASSIGNED"
                :assigned-count="$pullRequestCounts->assignedPullRequestCount"
                :open-route-name="\Maarheeze\Compass\Http\RouteName::GITHUB_PULL_REQUESTS"
                :open-count="$pullRequestCounts->openPullRequestCount">
                <x-slot:icon>
                    <x-compass::icons.git-pull-request />
                </x-slot:icon>
            </x-compass::assigned-and-open-counts>
        </div>

        <section class="overflow-hidden rounded-md border border-border bg-canvas">
            <div class="px-4 py-3">
                <x-compass::section-heading>
                    <x-slot:icon>
                        <x-compass::icons.alert />
                    </x-slot:icon>
                    Alerts
                </x-compass::section-heading>
            </div>

            @if ($alerts->isEmpty())
                <p class="flex items-center gap-2 border-t border-border px-4 py-3 text-sm text-emerald-700 dark:text-emerald-400">
                    <x-compass::icons.check-circle />
                    No alerts
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
