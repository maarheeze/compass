<x-compass::layouts.app :title="$repository->name">
    <x-compass::navigation :section="\Maarheeze\Compass\View\NavigationSection::REPOSITORIES" />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <div class="space-y-1">
            <div class="flex items-center gap-3">
                <h1 class="text-lg font-semibold text-foreground">{{ $repository->name }}</h1>
                <x-compass::composer.warning-badge :repository="$repository" />
                <a href="{{ $repository->url }}" target="_blank" rel="noopener" class="ml-auto text-sm text-foreground-muted hover:underline">GitHub &nearr;</a>
            </div>
            <p class="flex flex-wrap items-center gap-x-2 text-sm text-foreground-muted">
                <span>
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
                </span>
                <span>&middot;</span>
                <span class="lowercase">{{ $repository->visibility->value }}</span>
                <span>&middot;</span>
                <span>{{ $repository->default_branch }}</span>
                @if ($repository->composerJson?->php_constraint)
                    <span>&middot;</span>
                    <span>PHP {{ $repository->composerJson->php_constraint }}</span>
                @endif
                <span>&middot;</span>
                <span>
                    collected
                    <x-compass::time :time="$repository->collected_at" />
                </span>
            </p>
        </div>

        <section class="space-y-3">
            <x-compass::section-heading>
                <x-slot:icon>
                    <x-compass::icons.server />
                </x-slot:icon>
                Forge sites
                <span class="rounded-full bg-border/50 px-2 text-xs font-medium text-foreground-muted">{{ $repository->sites->count() }}</span>
            </x-compass::section-heading>

            <div class="overflow-hidden rounded-md border border-border bg-canvas">
                <table class="w-full table-fixed text-left text-sm">
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
                        @forelse ($repository->sites as $site)
                            <tr>
                                <td class="truncate px-4 py-2 text-foreground">
                                    <a href="{{ route(\Maarheeze\Compass\Http\RouteName::FORGE_SITE, $site) }}" class="text-accent hover:underline">{{ $site->name }}</a>
                                </td>
                                <td class="truncate px-4 py-2 text-foreground-muted">{{ $site->repository_branch }}</td>
                                <td class="px-4 py-2 text-foreground-muted">{{ $site->deployment_status?->value }}</td>
                                <td class="px-4 py-2 text-foreground-muted">
                                    <x-compass::commit-hash :repository="$site->repository" :commit-hash="$site->deployed_commit_hash" />
                                </td>
                                <td class="px-4 py-2">
                                    <x-compass::forge.deployment-state-label :site="$site" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-foreground-muted">No Forge sites deploy this repository.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="space-y-3">
            <x-compass::section-heading>
                <x-slot:icon>
                    <x-compass::icons.git-pull-request />
                </x-slot:icon>
                Pull requests
                <span class="rounded-full bg-border/50 px-2 text-xs font-medium text-foreground-muted">{{ $repository->pullRequests->count() }}</span>
            </x-compass::section-heading>

            <div class="overflow-hidden rounded-md border border-border bg-canvas">
                <table class="w-full table-fixed text-left text-sm">
                    <thead class="bg-canvas-subtle text-xs text-foreground-muted">
                        <tr>
                            <x-compass::pull-request-heading-cells />
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($repository->pullRequests as $pullRequest)
                            <tr>
                                <x-compass::pull-request-cells :pull-request="$pullRequest" />
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-foreground-muted">No open pull requests.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="space-y-3">
            <x-compass::section-heading>
                <x-slot:icon>
                    <x-compass::icons.issue-opened />
                </x-slot:icon>
                Issues
                <span class="rounded-full bg-border/50 px-2 text-xs font-medium text-foreground-muted">{{ $repository->issues->count() }}</span>
            </x-compass::section-heading>

            <div class="overflow-hidden rounded-md border border-border bg-canvas">
                <table class="w-full table-fixed text-left text-sm">
                    <thead class="bg-canvas-subtle text-xs text-foreground-muted">
                        <tr>
                            <x-compass::issue-heading-cells />
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($repository->issues as $issue)
                            <tr>
                                <x-compass::issue-cells :issue="$issue" />
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-foreground-muted">No open issues.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

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
