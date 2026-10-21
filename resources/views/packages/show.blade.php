<x-compass::layouts.app :title="$packageName">
    <main class="mx-auto max-w-5xl space-y-6 p-6">
        <x-compass::navigation />

        <h1 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $packageName }}</h1>

        <section class="space-y-3">
            <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Projects</h2>

            @if ($packageUsages->lockedPackages->isEmpty())
                <p class="text-sm text-zinc-500 dark:text-zinc-400">No project has this package locked.</p>
            @else
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-zinc-950/5 dark:bg-zinc-900 dark:ring-white/10">
                    <table class="w-full table-fixed text-left text-sm">
                        <thead class="bg-zinc-50 text-xs text-zinc-500 dark:bg-zinc-800/50 dark:text-zinc-400">
                            <tr>
                                <th class="px-4 py-2 font-medium">Repository</th>
                                <th class="w-40 px-4 py-2 font-medium">Version</th>
                                <th class="w-32 px-4 py-2 font-medium">Section</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-white/5">
                            @foreach ($packageUsages->lockedPackages as $lockedPackage)
                                <tr>
                                    <td class="truncate px-4 py-2 text-zinc-900 dark:text-zinc-100">
                                        <a href="{{ route(\Maarheeze\Compass\Http\RouteName::GITHUB_REPOSITORY, $lockedPackage->composerLock->repository) }}" class="hover:underline">{{ $lockedPackage->composerLock->repository->name }}</a>
                                    </td>
                                    <td class="px-4 py-2 text-zinc-500 dark:text-zinc-400">{{ $lockedPackage->version }}</td>
                                    <td class="px-4 py-2 text-zinc-500 lowercase dark:text-zinc-400">{{ $lockedPackage->section->value }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="space-y-3">
            <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Libraries</h2>

            @if ($packageUsages->requiredPackages->isEmpty())
                <p class="text-sm text-zinc-500 dark:text-zinc-400">No library requires this package.</p>
            @else
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-zinc-950/5 dark:bg-zinc-900 dark:ring-white/10">
                    <table class="w-full table-fixed text-left text-sm">
                        <thead class="bg-zinc-50 text-xs text-zinc-500 dark:bg-zinc-800/50 dark:text-zinc-400">
                            <tr>
                                <th class="px-4 py-2 font-medium">Repository</th>
                                <th class="w-40 px-4 py-2 font-medium">Constraint</th>
                                <th class="w-32 px-4 py-2 font-medium">Section</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-white/5">
                            @foreach ($packageUsages->requiredPackages as $requiredPackage)
                                <tr>
                                    <td class="truncate px-4 py-2 text-zinc-900 dark:text-zinc-100">
                                        <a href="{{ route(\Maarheeze\Compass\Http\RouteName::GITHUB_REPOSITORY, $requiredPackage->composerJson->repository) }}" class="hover:underline">{{ $requiredPackage->composerJson->repository->name }}</a>
                                    </td>
                                    <td class="px-4 py-2 text-zinc-500 dark:text-zinc-400">{{ $requiredPackage->constraint }}</td>
                                    <td class="px-4 py-2 text-zinc-500 lowercase dark:text-zinc-400">{{ $requiredPackage->section->value }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </main>
</x-compass::layouts.app>
