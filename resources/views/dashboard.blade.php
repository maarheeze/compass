<x-compass::layouts.app :title="__('Dashboard')">
    <x-compass::navigation :section="\Maarheeze\Compass\View\NavigationSection::DASHBOARD" />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <div class="grid gap-4 sm:grid-cols-2">
            <x-compass::assigned-and-open-counts
                :title="__('Issues')"
                :assigned-route-name="\Maarheeze\Compass\Http\RouteName::GITHUB_ISSUES_ASSIGNED"
                :assigned-count="$issueCounts->assignedIssueCount"
                :open-route-name="\Maarheeze\Compass\Http\RouteName::GITHUB_ISSUES"
                :open-count="$issueCounts->openIssueCount" />
            <x-compass::assigned-and-open-counts
                :title="__('Pull requests')"
                :assigned-route-name="\Maarheeze\Compass\Http\RouteName::GITHUB_PULL_REQUESTS_ASSIGNED"
                :assigned-count="$pullRequestCounts->assignedPullRequestCount"
                :open-route-name="\Maarheeze\Compass\Http\RouteName::GITHUB_PULL_REQUESTS"
                :open-count="$pullRequestCounts->openPullRequestCount" />
        </div>

        @if ($alerts->isEmpty())
            <p class="text-sm text-foreground-muted">No alerts.</p>
        @else
            @if ($alerts->repositoriesWithComposerWarning->isNotEmpty())
                <section class="space-y-3">
                    <h2 class="text-sm font-semibold text-foreground">Composer warnings</h2>

                    <ul class="divide-y divide-border overflow-hidden rounded-md border border-border bg-canvas">
                        @foreach ($alerts->repositoriesWithComposerWarning as $repository)
                            <li class="flex items-center justify-between px-4 py-2 text-sm">
                                <a href="{{ route(\Maarheeze\Compass\Http\RouteName::GITHUB_REPOSITORY, $repository) }}" class="font-medium text-accent hover:underline">{{ $repository->name }}</a>
                                <x-compass::composer.warning-badge :repository="$repository" />
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($alerts->sitesBehind->isNotEmpty())
                <section class="space-y-3">
                    <h2 class="text-sm font-semibold text-foreground">Sites behind</h2>

                    <ul class="divide-y divide-border overflow-hidden rounded-md border border-border bg-canvas">
                        @foreach ($alerts->sitesBehind as $site)
                            <li class="flex items-center justify-between gap-4 px-4 py-2 text-sm">
                                <a href="{{ route(\Maarheeze\Compass\Http\RouteName::FORGE_SITE, $site) }}" class="font-medium text-accent hover:underline">{{ $site->name }}</a>
                                <code class="ml-auto block w-[7ch] overflow-hidden text-foreground-muted" title="{{ $site->deployed_commit_hash }}">{{ $site->deployed_commit_hash }}</code>
                                <x-compass::forge.link :site="$site" />
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        @endif
    </main>
</x-compass::layouts.app>
