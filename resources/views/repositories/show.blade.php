<x-compass::layouts.app :title="$repository->name">
    <main class="mx-auto max-w-5xl space-y-6 p-6">
        <x-compass::navigation />

        <div class="flex items-center gap-3">
            <h1 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $repository->name }}</h1>
            <x-compass::composer.warning-badge :repository="$repository" />
            <a href="{{ $repository->url }}" target="_blank" rel="noopener" class="ml-auto text-sm text-zinc-500 hover:underline dark:text-zinc-400">GitHub &nearr;</a>
        </div>

        <section class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-zinc-950/5 dark:bg-zinc-900 dark:ring-white/10">
            <dl class="grid grid-cols-[max-content_1fr] gap-x-6 gap-y-2 text-sm">
                <dt class="text-zinc-500 dark:text-zinc-400">Kind</dt>
                <dd>
                    @switch ($kind)
                        @case (\Maarheeze\Compass\Analyzer\Composer\RepositoryKind::LIBRARY)
                            Library
                            @break
                        @case (\Maarheeze\Compass\Analyzer\Composer\RepositoryKind::OTHER)
                            Other
                            @break
                        @case (\Maarheeze\Compass\Analyzer\Composer\RepositoryKind::PROJECT)
                            Project
                            @break
                    @endswitch
                </dd>
                <dt class="text-zinc-500 dark:text-zinc-400">Visibility</dt>
                <dd class="lowercase">{{ $repository->visibility->value }}</dd>
                <dt class="text-zinc-500 dark:text-zinc-400">Branch</dt>
                <dd>{{ $repository->default_branch }}</dd>
                <dt class="text-zinc-500 dark:text-zinc-400">Last commit</dt>
                <dd><code>{{ $repository->last_commit_sha }}</code></dd>
                <dt class="text-zinc-500 dark:text-zinc-400">composer.json</dt>
                <dd><code>{{ $repository->composer_json_oid }}</code></dd>
                <dt class="text-zinc-500 dark:text-zinc-400">composer.lock</dt>
                <dd><code>{{ $repository->composer_lock_oid }}</code></dd>
                <dt class="text-zinc-500 dark:text-zinc-400">Collected at</dt>
                <dd>
                    <x-compass::time :time="$repository->collected_at" />
                </dd>
            </dl>
        </section>

        @if ($repository->sites->isEmpty())
            <p class="text-sm text-zinc-500 dark:text-zinc-400">No Forge sites deploy this repository.</p>
        @else
            <details class="group rounded-xl bg-white shadow-sm ring-1 ring-zinc-950/5 dark:bg-zinc-900 dark:ring-white/10">
                <summary class="flex cursor-pointer items-center gap-2 px-4 py-3 text-sm">
                    <span class="text-zinc-400 transition group-open:rotate-90">&rsaquo;</span>
                    <span class="font-semibold text-zinc-900 dark:text-zinc-100">Forge sites</span>
                    <span class="text-zinc-500 dark:text-zinc-400">&middot; {{ $repository->sites->count() }} sites</span>
                </summary>

                <table class="w-full table-fixed border-t border-zinc-100 text-left text-sm dark:border-white/5">
                    <thead class="bg-zinc-50 text-xs text-zinc-500 dark:bg-zinc-800/50 dark:text-zinc-400">
                        <tr>
                            <th class="px-4 py-2 font-medium">Site</th>
                            <th class="w-28 px-4 py-2 font-medium">Branch</th>
                            <th class="w-28 px-4 py-2 font-medium">Deployment</th>
                            <th class="w-28 px-4 py-2 font-medium">Deployed commit</th>
                            <th class="w-28 px-4 py-2 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-white/5">
                        @foreach ($repository->sites as $site)
                            <tr>
                                <td class="truncate px-4 py-2 text-zinc-900 dark:text-zinc-100">
                                    <a href="{{ route(\Maarheeze\Compass\Http\RouteName::FORGE_SITE, $site) }}" class="hover:underline">{{ $site->name }}</a>
                                </td>
                                <td class="truncate px-4 py-2 text-zinc-500 dark:text-zinc-400">{{ $site->repository_branch }}</td>
                                <td class="px-4 py-2 text-zinc-500 dark:text-zinc-400">{{ $site->deployment_status?->value }}</td>
                                <td class="px-4 py-2 text-zinc-500 dark:text-zinc-400">
                                    <code class="block w-[7ch] overflow-hidden" title="{{ $site->deployed_commit_hash }}">{{ $site->deployed_commit_hash }}</code>
                                </td>
                                <td class="px-4 py-2">
                                    <x-compass::forge.deployment-state-label :site="$site" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </details>
        @endif

        @if ($repository->composerJson)
            <details class="group rounded-xl bg-white shadow-sm ring-1 ring-zinc-950/5 dark:bg-zinc-900 dark:ring-white/10">
                <summary class="flex cursor-pointer items-center gap-2 px-4 py-3 text-sm">
                    <span class="text-zinc-400 transition group-open:rotate-90">&rsaquo;</span>
                    <span class="font-semibold text-zinc-900 dark:text-zinc-100">composer.json</span>
                    @if ($repository->composerJson->type)
                        <span class="text-zinc-500 dark:text-zinc-400">&middot; {{ $repository->composerJson->type }}</span>
                    @endif
                    @if ($repository->composerJson->php_constraint)
                        <span class="text-zinc-500 dark:text-zinc-400">&middot; PHP {{ $repository->composerJson->php_constraint }}</span>
                    @endif
                    <span class="text-zinc-500 dark:text-zinc-400">&middot; {{ $repository->composerJson->productionPackages->count() }} production</span>
                    <span class="text-zinc-500 dark:text-zinc-400">&middot; {{ $repository->composerJson->developmentPackages->count() }} development</span>
                </summary>

                <table class="w-full table-fixed border-t border-zinc-100 text-left text-sm dark:border-white/5">
                    <thead class="bg-zinc-50 text-xs text-zinc-500 dark:bg-zinc-800/50 dark:text-zinc-400">
                        <tr>
                            <th class="px-4 py-2 font-medium">Package</th>
                            <th class="w-40 px-4 py-2 font-medium">Constraint</th>
                        </tr>
                    </thead>
                    @if ($repository->composerJson->productionPackages->isNotEmpty())
                        <tbody class="divide-y divide-zinc-100 dark:divide-white/5">
                            <tr>
                                <th colspan="2" class="bg-zinc-50/50 px-4 py-2 text-xs font-medium text-zinc-500 dark:bg-zinc-800/30 dark:text-zinc-400">Production</th>
                            </tr>
                            @foreach ($repository->composerJson->productionPackages as $package)
                                <tr>
                                    <td class="truncate px-4 py-2 text-zinc-900 dark:text-zinc-100">
                                        <x-compass::composer.package-link :package-name="$package->name" />
                                    </td>
                                    <td class="px-4 py-2 text-zinc-500 dark:text-zinc-400">{{ $package->constraint }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endif
                    @if ($repository->composerJson->developmentPackages->isNotEmpty())
                        <tbody class="divide-y divide-zinc-100 border-t border-zinc-100 dark:divide-white/5 dark:border-white/5">
                            <tr>
                                <th colspan="2" class="bg-zinc-50/50 px-4 py-2 text-xs font-medium text-zinc-500 dark:bg-zinc-800/30 dark:text-zinc-400">Development</th>
                            </tr>
                            @foreach ($repository->composerJson->developmentPackages as $package)
                                <tr>
                                    <td class="truncate px-4 py-2 text-zinc-900 dark:text-zinc-100">
                                        <x-compass::composer.package-link :package-name="$package->name" />
                                    </td>
                                    <td class="px-4 py-2 text-zinc-500 dark:text-zinc-400">{{ $package->constraint }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endif
                </table>
            </details>
        @else
            <p class="text-sm text-zinc-500 dark:text-zinc-400">No composer.json collected.</p>
        @endif

        @if ($repository->composerLock)
            <details class="group rounded-xl bg-white shadow-sm ring-1 ring-zinc-950/5 dark:bg-zinc-900 dark:ring-white/10">
                <summary class="flex cursor-pointer items-center gap-2 px-4 py-3 text-sm">
                    <span class="text-zinc-400 transition group-open:rotate-90">&rsaquo;</span>
                    <span class="font-semibold text-zinc-900 dark:text-zinc-100">composer.lock</span>
                    @if ($repository->composerLock->php_constraint)
                        <span class="text-zinc-500 dark:text-zinc-400">&middot; PHP {{ $repository->composerLock->php_constraint }}</span>
                    @endif
                    <span class="text-zinc-500 dark:text-zinc-400">&middot; {{ $repository->composerLock->packages->count() }} packages</span>
                </summary>

                <table class="w-full table-fixed border-t border-zinc-100 text-left text-sm dark:border-white/5">
                    <thead class="bg-zinc-50 text-xs text-zinc-500 dark:bg-zinc-800/50 dark:text-zinc-400">
                        <tr>
                            <th class="px-4 py-2 font-medium">Package</th>
                            <th class="w-40 px-4 py-2 font-medium">Version</th>
                            <th class="w-32 px-4 py-2 font-medium">Section</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-white/5">
                        @foreach ($repository->composerLock->packages as $package)
                            <tr>
                                <td class="truncate px-4 py-2 text-zinc-900 dark:text-zinc-100">
                                    <x-compass::composer.package-link :package-name="$package->name" />
                                </td>
                                <td class="px-4 py-2 text-zinc-500 dark:text-zinc-400">{{ $package->version }}</td>
                                <td class="px-4 py-2 text-zinc-500 lowercase dark:text-zinc-400">{{ $package->section->value }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </details>
        @else
            <p class="text-sm text-zinc-500 dark:text-zinc-400">No composer.lock collected.</p>
        @endif
    </main>
</x-compass::layouts.app>
