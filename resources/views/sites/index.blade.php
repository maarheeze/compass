<x-compass::layouts.app :title="__('Sites')">
    <main class="mx-auto max-w-5xl space-y-6 p-6">
        <x-compass::navigation />

        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-zinc-950/5 dark:bg-zinc-900 dark:ring-white/10">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 text-xs text-zinc-500 dark:bg-zinc-800/50 dark:text-zinc-400">
                    <tr>
                        <th class="px-4 py-2 font-medium">Site</th>
                        <th class="px-4 py-2 font-medium">Server</th>
                        <th class="px-4 py-2 font-medium">Status</th>
                        <th class="px-4 py-2 font-medium">Deployed commit</th>
                        <th class="px-4 py-2 font-medium">Collected at</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-white/5">
                    @forelse ($sites as $site)
                        <tr>
                            <td class="px-4 py-2 font-medium text-zinc-900 dark:text-zinc-100">
                                <a href="{{ route(\Maarheeze\Compass\Http\RouteName::FORGE_SITE, $site) }}" class="hover:underline">{{ $site->name }}</a>
                            </td>
                            <td class="px-4 py-2 text-zinc-500 dark:text-zinc-400">{{ $site->server_name }}</td>
                            <td class="px-4 py-2">
                                <x-compass::forge.deployment-state-label :site="$site" />
                            </td>
                            <td class="px-4 py-2 text-zinc-500 dark:text-zinc-400">
                                <code class="block w-[7ch] overflow-hidden" title="{{ $site->deployed_commit_hash }}">{{ $site->deployed_commit_hash }}</code>
                            </td>
                            <td class="px-4 py-2 text-zinc-500 dark:text-zinc-400">
                                <x-compass::time :time="$site->collected_at" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-zinc-500 dark:text-zinc-400">No sites collected yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</x-compass::layouts.app>
