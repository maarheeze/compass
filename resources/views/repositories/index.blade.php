<x-compass::layouts.app :title="__('Repositories')">
    <main class="mx-auto max-w-5xl space-y-6 p-6">
        <x-compass::navigation :user="$user" />

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
                            <td class="px-4 py-2 font-medium text-zinc-900 dark:text-zinc-100">
                                <a href="{{ route(\Maarheeze\Compass\Http\RouteName::GITHUB_REPOSITORY, $repository) }}" class="hover:underline">{{ $repository->name }}</a>
                            </td>
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
