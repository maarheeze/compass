<x-compass::layouts.app :title="__('Pull requests')">
    <main class="mx-auto max-w-5xl space-y-6 p-6">
        <x-compass::navigation />

        <nav class="flex items-center gap-6 border-b border-zinc-200 text-sm font-medium dark:border-white/10">
            <x-compass::tab :route-name="\Maarheeze\Compass\Http\RouteName::GITHUB_PULL_REQUESTS_ASSIGNED" :active-route-name="$activeRouteName">Assigned to me</x-compass::tab>
            <x-compass::tab :route-name="\Maarheeze\Compass\Http\RouteName::GITHUB_PULL_REQUESTS" :active-route-name="$activeRouteName">All open</x-compass::tab>
        </nav>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-zinc-950/5 dark:bg-zinc-900 dark:ring-white/10">
            <table class="w-full table-fixed text-left text-sm">
                <thead class="bg-zinc-50 text-xs text-zinc-500 dark:bg-zinc-800/50 dark:text-zinc-400">
                    <tr>
                        <th class="w-48 px-4 py-2 font-medium">Repository</th>
                        <th class="px-4 py-2 font-medium">Pull request</th>
                        <th class="w-40 px-4 py-2 font-medium">Assignees</th>
                        <th class="w-32 px-4 py-2 font-medium">Opened</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-white/5">
                    @forelse ($pullRequests as $pullRequest)
                        <tr>
                            <td class="truncate px-4 py-2 text-zinc-500 dark:text-zinc-400">
                                <a href="{{ route(\Maarheeze\Compass\Http\RouteName::GITHUB_REPOSITORY, $pullRequest->repository) }}" class="hover:underline">{{ $pullRequest->repository->name }}</a>
                            </td>
                            <td class="truncate px-4 py-2 text-zinc-900 dark:text-zinc-100">
                                @if ($pullRequest->is_draft)
                                    <span class="mr-1 rounded bg-zinc-100 px-1.5 py-0.5 text-xs text-zinc-500 dark:bg-white/10 dark:text-zinc-400">Draft</span>
                                @endif
                                <a href="{{ $pullRequest->url }}" target="_blank" rel="noopener" class="hover:underline">
                                    {{ $pullRequest->title }}
                                    <span class="text-zinc-400 dark:text-zinc-500">#{{ $pullRequest->number }}</span>
                                    &nearr;
                                </a>
                            </td>
                            <td class="truncate px-4 py-2 text-zinc-500 dark:text-zinc-400">
                                @foreach ($pullRequest->assignees as $assignee)
                                    <span>{{ $assignee->login }}</span>
                                @endforeach
                            </td>
                            <td class="px-4 py-2 text-zinc-500 dark:text-zinc-400">
                                <x-compass::time :time="$pullRequest->opened_at" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-zinc-500 dark:text-zinc-400">No open pull requests.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</x-compass::layouts.app>
