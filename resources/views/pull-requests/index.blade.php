<x-compass::layouts.app :title="__('Pull requests')">
    <x-compass::navigation :section="\Maarheeze\Compass\View\NavigationSection::PULL_REQUESTS" />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <nav class="flex items-center gap-6 border-b border-border text-sm font-medium">
            <x-compass::tab :route-name="\Maarheeze\Compass\Http\RouteName::GITHUB_PULL_REQUESTS" :is-active="$activeRouteName === \Maarheeze\Compass\Http\RouteName::GITHUB_PULL_REQUESTS">
                <x-compass::icons.git-pull-request class="text-foreground-muted" />
                All open
            </x-compass::tab>
            <x-compass::tab :route-name="\Maarheeze\Compass\Http\RouteName::GITHUB_PULL_REQUESTS_ASSIGNED" :is-active="$activeRouteName === \Maarheeze\Compass\Http\RouteName::GITHUB_PULL_REQUESTS_ASSIGNED">
                <x-compass::icons.person class="text-foreground-muted" />
                Assigned to me
            </x-compass::tab>
        </nav>

        <div class="overflow-hidden rounded-md border border-border bg-canvas">
            <table class="w-full table-fixed text-left text-sm">
                <thead class="bg-canvas-subtle text-xs text-foreground-muted">
                    <tr>
                        <th class="w-48 px-4 py-2 font-medium">Repository</th>
                        <x-compass::pull-request-heading-cells />
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($pullRequests as $pullRequest)
                        <tr>
                            <td class="truncate px-4 py-2 text-foreground-muted">
                                <a href="{{ route(\Maarheeze\Compass\Http\RouteName::GITHUB_REPOSITORY, $pullRequest->repository) }}" class="text-accent hover:underline">{{ $pullRequest->repository->name }}</a>
                            </td>
                            <x-compass::pull-request-cells :pull-request="$pullRequest" />
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-foreground-muted">No open pull requests.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</x-compass::layouts.app>
