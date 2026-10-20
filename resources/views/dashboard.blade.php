<x-compass::layouts.app :title="__('Dashboard')">
    <main class="mx-auto max-w-5xl space-y-6 p-6">
        <header class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if ($user->avatar_url)
                    <img src="{{ $user->avatar_url }}" alt="" class="size-9 rounded-full">
                @endif
                <div>
                    <h1 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                        @if ($user->name)
                            {{ $user->name }}
                        @else
                            {{ $user->nickname }}
                        @endif
                    </h1>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">&#64;{{ $user->nickname }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route(\Maarheeze\Compass\Http\RouteName::SYNC_GITHUB) }}">
                    @csrf
                    <x-compass::button>Sync GitHub</x-compass::button>
                </form>

                <form method="POST" action="{{ route(\Maarheeze\Compass\Http\RouteName::LOGOUT) }}">
                    @csrf
                    <x-compass::button>Sign out</x-compass::button>
                </form>
            </div>
        </header>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-zinc-950/5 dark:bg-zinc-900 dark:ring-white/10">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 text-xs text-zinc-500 dark:bg-zinc-800/50 dark:text-zinc-400">
                    <tr>
                        <th class="px-4 py-2 font-medium">Repository</th>
                        <th class="px-4 py-2 font-medium">Visibility</th>
                        <th class="px-4 py-2 font-medium">Branch</th>
                        <th class="px-4 py-2 font-medium">Last commit</th>
                        <th class="px-4 py-2 font-medium">Collected at</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-white/5">
                    @forelse ($repositories as $repository)
                        <tr>
                            <td class="px-4 py-2 font-medium text-zinc-900 dark:text-zinc-100">{{ $repository->name }}</td>
                            <td class="px-4 py-2 text-zinc-500 lowercase dark:text-zinc-400">{{ $repository->visibility->value }}</td>
                            <td class="px-4 py-2 text-zinc-500 dark:text-zinc-400">{{ $repository->default_branch }}</td>
                            <td class="px-4 py-2 text-zinc-500 dark:text-zinc-400">
                                <code class="block w-[7ch] overflow-hidden" title="{{ $repository->last_commit_sha }}">{{ $repository->last_commit_sha }}</code>
                            </td>
                            <td class="px-4 py-2 text-zinc-500 dark:text-zinc-400">
                                <x-compass::time :time="$repository->collected_at" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-zinc-500 dark:text-zinc-400">No repositories collected yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</x-compass::layouts.app>
