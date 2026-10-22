<x-compass::layouts.app :title="$repository->name">
    <x-compass::navigation :section="\Maarheeze\Compass\View\NavigationSection::REPOSITORIES" />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <div class="flex items-center gap-3">
            <h1 class="text-lg font-semibold text-foreground">{{ $repository->name }}</h1>
            <x-compass::composer.warning-badge :repository="$repository" />
            <a href="{{ $repository->url }}" target="_blank" rel="noopener" class="ml-auto text-sm text-foreground-muted hover:underline">GitHub &nearr;</a>
        </div>

        <section class="rounded-md border border-border bg-canvas p-4">
            <dl class="grid grid-cols-[max-content_1fr] gap-x-6 gap-y-2 text-sm">
                <dt class="text-foreground-muted">Kind</dt>
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
                <dt class="text-foreground-muted">Visibility</dt>
                <dd class="lowercase">{{ $repository->visibility->value }}</dd>
                <dt class="text-foreground-muted">Branch</dt>
                <dd>{{ $repository->default_branch }}</dd>
                <dt class="text-foreground-muted">Last commit</dt>
                <dd><code>{{ $repository->last_commit_sha }}</code></dd>
                <dt class="text-foreground-muted">composer.json</dt>
                <dd><code>{{ $repository->composer_json_oid }}</code></dd>
                <dt class="text-foreground-muted">composer.lock</dt>
                <dd><code>{{ $repository->composer_lock_oid }}</code></dd>
                <dt class="text-foreground-muted">Collected at</dt>
                <dd>
                    <x-compass::time :time="$repository->collected_at" />
                </dd>
            </dl>
        </section>

        @if ($repository->sites->isEmpty())
            <p class="text-sm text-foreground-muted">No Forge sites deploy this repository.</p>
        @else
            <details class="group rounded-md border border-border bg-canvas">
                <summary class="flex cursor-pointer items-center gap-2 px-4 py-3 text-sm">
                    <span class="text-foreground-muted transition group-open:rotate-90">&rsaquo;</span>
                    <x-compass::section-heading>
                        <x-slot:icon>
                            <x-compass::icons.server />
                        </x-slot:icon>
                        Forge sites
                    </x-compass::section-heading>
                    <span class="text-foreground-muted">&middot; {{ $repository->sites->count() }} sites</span>
                </summary>

                <table class="w-full table-fixed border-t border-border text-left text-sm">
                    <thead class="bg-canvas-subtle text-xs text-foreground-muted">
                        <tr>
                            <th class="px-4 py-2 font-medium">Site</th>
                            <th class="w-28 px-4 py-2 font-medium">Branch</th>
                            <th class="w-28 px-4 py-2 font-medium">Deployment</th>
                            <th class="w-28 px-4 py-2 font-medium">Deployed commit</th>
                            <th class="w-28 px-4 py-2 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($repository->sites as $site)
                            <tr>
                                <td class="truncate px-4 py-2 text-foreground">
                                    <a href="{{ route(\Maarheeze\Compass\Http\RouteName::FORGE_SITE, $site) }}" class="text-accent hover:underline">{{ $site->name }}</a>
                                </td>
                                <td class="truncate px-4 py-2 text-foreground-muted">{{ $site->repository_branch }}</td>
                                <td class="px-4 py-2 text-foreground-muted">{{ $site->deployment_status?->value }}</td>
                                <td class="px-4 py-2 text-foreground-muted">
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

        @if ($repository->issues->isEmpty())
            <p class="text-sm text-foreground-muted">No open issues.</p>
        @else
            <details class="group rounded-md border border-border bg-canvas">
                <summary class="flex cursor-pointer items-center gap-2 px-4 py-3 text-sm">
                    <span class="text-foreground-muted transition group-open:rotate-90">&rsaquo;</span>
                    <x-compass::section-heading>
                        <x-slot:icon>
                            <x-compass::icons.issue-opened />
                        </x-slot:icon>
                        Issues
                    </x-compass::section-heading>
                    <span class="text-foreground-muted">&middot; {{ $repository->issues->count() }} open</span>
                </summary>

                <table class="w-full table-fixed border-t border-border text-left text-sm">
                    <thead class="bg-canvas-subtle text-xs text-foreground-muted">
                        <tr>
                            <x-compass::issue-heading-cells />
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($repository->issues as $issue)
                            <tr>
                                <x-compass::issue-cells :issue="$issue" />
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </details>
        @endif

        @if ($repository->pullRequests->isEmpty())
            <p class="text-sm text-foreground-muted">No open pull requests.</p>
        @else
            <details class="group rounded-md border border-border bg-canvas">
                <summary class="flex cursor-pointer items-center gap-2 px-4 py-3 text-sm">
                    <span class="text-foreground-muted transition group-open:rotate-90">&rsaquo;</span>
                    <x-compass::section-heading>
                        <x-slot:icon>
                            <x-compass::icons.git-pull-request />
                        </x-slot:icon>
                        Pull requests
                    </x-compass::section-heading>
                    <span class="text-foreground-muted">&middot; {{ $repository->pullRequests->count() }} open</span>
                </summary>

                <table class="w-full table-fixed border-t border-border text-left text-sm">
                    <thead class="bg-canvas-subtle text-xs text-foreground-muted">
                        <tr>
                            <x-compass::pull-request-heading-cells />
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($repository->pullRequests as $pullRequest)
                            <tr>
                                <x-compass::pull-request-cells :pull-request="$pullRequest" />
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </details>
        @endif

        @if ($repository->composerJson)
            <details class="group rounded-md border border-border bg-canvas">
                <summary class="flex cursor-pointer items-center gap-2 px-4 py-3 text-sm">
                    <span class="text-foreground-muted transition group-open:rotate-90">&rsaquo;</span>
                    <x-compass::section-heading>
                        <x-slot:icon>
                            <x-compass::icons.file-code />
                        </x-slot:icon>
                        composer.json
                    </x-compass::section-heading>
                    @if ($repository->composerJson->type)
                        <span class="text-foreground-muted">&middot; {{ $repository->composerJson->type }}</span>
                    @endif
                    @if ($repository->composerJson->php_constraint)
                        <span class="text-foreground-muted">&middot; PHP {{ $repository->composerJson->php_constraint }}</span>
                    @endif
                    <span class="text-foreground-muted">&middot; {{ $repository->composerJson->productionPackages->count() }} production</span>
                    <span class="text-foreground-muted">&middot; {{ $repository->composerJson->developmentPackages->count() }} development</span>
                </summary>

                <table class="w-full table-fixed border-t border-border text-left text-sm">
                    <thead class="bg-canvas-subtle text-xs text-foreground-muted">
                        <tr>
                            <th class="px-4 py-2 font-medium">Package</th>
                            <th class="w-40 px-4 py-2 font-medium">Constraint</th>
                        </tr>
                    </thead>
                    @if ($repository->composerJson->productionPackages->isNotEmpty())
                        <tbody class="divide-y divide-border">
                            <tr>
                                <th colspan="2" class="bg-canvas-subtle px-4 py-2 text-xs font-medium text-foreground-muted">Production</th>
                            </tr>
                            @foreach ($repository->composerJson->productionPackages as $package)
                                <tr>
                                    <td class="truncate px-4 py-2 text-foreground">
                                        <x-compass::composer.package-link :package-name="$package->name" />
                                    </td>
                                    <td class="px-4 py-2 text-foreground-muted">{{ $package->constraint }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endif
                    @if ($repository->composerJson->developmentPackages->isNotEmpty())
                        <tbody class="divide-y divide-border border-t border-border">
                            <tr>
                                <th colspan="2" class="bg-canvas-subtle px-4 py-2 text-xs font-medium text-foreground-muted">Development</th>
                            </tr>
                            @foreach ($repository->composerJson->developmentPackages as $package)
                                <tr>
                                    <td class="truncate px-4 py-2 text-foreground">
                                        <x-compass::composer.package-link :package-name="$package->name" />
                                    </td>
                                    <td class="px-4 py-2 text-foreground-muted">{{ $package->constraint }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endif
                </table>
            </details>
        @else
            <p class="text-sm text-foreground-muted">No composer.json collected.</p>
        @endif

        @if ($repository->composerLock)
            <details class="group rounded-md border border-border bg-canvas">
                <summary class="flex cursor-pointer items-center gap-2 px-4 py-3 text-sm">
                    <span class="text-foreground-muted transition group-open:rotate-90">&rsaquo;</span>
                    <x-compass::section-heading>
                        <x-slot:icon>
                            <x-compass::icons.file-code />
                        </x-slot:icon>
                        composer.lock
                    </x-compass::section-heading>
                    @if ($repository->composerLock->php_constraint)
                        <span class="text-foreground-muted">&middot; PHP {{ $repository->composerLock->php_constraint }}</span>
                    @endif
                    <span class="text-foreground-muted">&middot; {{ $repository->composerLock->packages->count() }} packages</span>
                </summary>

                <table class="w-full table-fixed border-t border-border text-left text-sm">
                    <thead class="bg-canvas-subtle text-xs text-foreground-muted">
                        <tr>
                            <th class="px-4 py-2 font-medium">Package</th>
                            <th class="w-40 px-4 py-2 font-medium">Version</th>
                            <th class="w-32 px-4 py-2 font-medium">Section</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($repository->composerLock->packages as $package)
                            <tr>
                                <td class="truncate px-4 py-2 text-foreground">
                                    <x-compass::composer.package-link :package-name="$package->name" />
                                </td>
                                <td class="px-4 py-2 text-foreground-muted">{{ $package->version }}</td>
                                <td class="px-4 py-2 text-foreground-muted lowercase">{{ $package->section->value }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </details>
        @else
            <p class="text-sm text-foreground-muted">No composer.lock collected.</p>
        @endif
    </main>
</x-compass::layouts.app>
