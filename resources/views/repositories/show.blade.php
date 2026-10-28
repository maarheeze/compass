<x-compass::layouts.app :title="$repository->name">
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <x-compass::repository-missing-notice :repository="$repository" />

        <div class="space-y-1">
            <div class="flex items-center gap-3">
                <x-compass::subject-name :subject="$repository">
                    <h1 class="text-lg font-semibold text-foreground">{{ $repository->name }}</h1>
                </x-compass::subject-name>
                <a href="{{ $repository->url }}" target="_blank" rel="noopener" class="ml-auto text-sm text-foreground-muted hover:underline">GitHub &nearr;</a>
            </div>
            <p class="flex flex-wrap items-center gap-x-2 text-sm text-foreground-muted">
                <span>
                    <x-compass::enum-label :value="$kind" />
                </span>
                <span>&middot;</span>
                <span class="lowercase"><x-compass::enum-label :value="$repository->visibility" /></span>
                <span>&middot;</span>
                <span>{{ $repository->default_branch }}</span>
                @if ($repository->composerJson?->php_constraint)
                    <span>&middot;</span>
                    <span>PHP {{ $repository->composerJson->php_constraint }}</span>
                @endif
                <span>&middot;</span>
                <span>
                    {{ __('compass::repository.collected') }}
                    <x-compass::time :time="$repository->collected_at" />
                </span>
            </p>
        </div>

        <section class="space-y-3">
            <x-compass::section-heading>
                <x-slot:icon>
                    <x-compass::icons.alert />
                </x-slot:icon>
                {{ __('compass::alert.alerts') }}
            </x-compass::section-heading>
            <x-compass::alert-table :alerts="$repository->alerts" />
        </section>

        <section class="space-y-3">
            <x-compass::section-heading>
                <x-slot:icon>
                    <x-compass::icons.issue-opened />
                </x-slot:icon>
                {{ __('compass::navigation.issues') }}
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
                                <td colspan="3" class="px-4 py-6 text-center text-foreground-muted">{{ __('compass::issue.no_open_issues') }}</td>
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
                {{ __('compass::navigation.pull_requests') }}
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
                                <td colspan="3" class="px-4 py-6 text-center text-foreground-muted">{{ __('compass::pull-request.no_open_pull_requests') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="space-y-3">
            <x-compass::section-heading>
                <x-slot:icon>
                    <x-compass::icons.server />
                </x-slot:icon>
                {{ __('compass::repository.sites') }}
            </x-compass::section-heading>

            <div class="overflow-hidden rounded-md border border-border bg-canvas">
                <table class="w-full table-fixed text-left text-sm">
                    <thead class="bg-canvas-subtle text-xs text-foreground-muted">
                        <tr>
                            <th class="px-4 py-2 font-medium">{{ __('compass::repository.site') }}</th>
                            <th class="w-28 px-4 py-2 font-medium">{{ __('compass::repository.branch') }}</th>
                            <th class="w-28 px-4 py-2 font-medium">{{ __('compass::repository.deployment') }}</th>
                            <th class="w-28 px-4 py-2 font-medium">{{ __('compass::repository.deployed_commit') }}</th>
                            <th class="w-28 px-4 py-2 font-medium">{{ __('compass::repository.status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($repository->sites as $site)
                            <tr>
                                <td class="truncate px-4 py-2 text-foreground">
                                    <x-compass::site-link :site="$site" />
                                </td>
                                <td class="truncate px-4 py-2 text-foreground-muted">{{ $site->repository_branch }}</td>
                                <td class="px-4 py-2 text-foreground-muted">
                                    @if ($site->deployment_status !== null)
                                        <x-compass::enum-label :value="$site->deployment_status" />
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-foreground-muted">
                                    <x-compass::commit-hash :repository="$site->repository" :commit-hash="$site->deployed_commit_hash" />
                                </td>
                                <td class="px-4 py-2">
                                    <div class="flex items-center gap-2">
                                        <x-compass::missing-label :missing-at="$site->missing_at" :missing-ignored-at="$site->missing_ignored_at" />
                                        <x-compass::forge.deployment-state-badge :site="$site" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-foreground-muted">{{ __('compass::repository.no_sites') }}</td>
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
                    <span class="text-foreground-muted">&middot; {{ __('compass::composer.production_count', ['count' => $repository->composerJson->productionPackages->count()]) }}</span>
                    <span class="text-foreground-muted">&middot; {{ __('compass::composer.development_count', ['count' => $repository->composerJson->developmentPackages->count()]) }}</span>
                </summary>

                <table class="w-full table-fixed border-t border-border text-left text-sm">
                    <thead class="bg-canvas-subtle text-xs text-foreground-muted">
                        <tr>
                            <th class="px-4 py-2 font-medium">{{ __('compass::composer.package') }}</th>
                            <th class="w-40 px-4 py-2 font-medium">{{ __('compass::composer.constraint') }}</th>
                        </tr>
                    </thead>
                    @if ($repository->composerJson->productionPackages->isNotEmpty())
                        <tbody class="divide-y divide-border">
                            <tr>
                                <th colspan="2" class="bg-canvas-subtle px-4 py-2 text-xs font-medium text-foreground-muted">{{ __('compass::composer.production') }}</th>
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
                                <th colspan="2" class="bg-canvas-subtle px-4 py-2 text-xs font-medium text-foreground-muted">{{ __('compass::composer.development') }}</th>
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
            <p class="text-sm text-foreground-muted">{{ __('compass::composer.no_composer_json') }}</p>
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
                    <span class="text-foreground-muted">&middot; {{ __('compass::composer.package_count', ['count' => $repository->composerLock->packages->count()]) }}</span>
                </summary>

                <table class="w-full table-fixed border-t border-border text-left text-sm">
                    <thead class="bg-canvas-subtle text-xs text-foreground-muted">
                        <tr>
                            <th class="px-4 py-2 font-medium">{{ __('compass::composer.package') }}</th>
                            <th class="w-40 px-4 py-2 font-medium">{{ __('compass::composer.version') }}</th>
                            <th class="w-32 px-4 py-2 font-medium">{{ __('compass::composer.section') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($repository->composerLock->packages as $package)
                            <tr>
                                <td class="truncate px-4 py-2 text-foreground">
                                    <x-compass::composer.package-link :package-name="$package->name" />
                                </td>
                                <td class="px-4 py-2 text-foreground-muted">{{ $package->version }}</td>
                                <td class="px-4 py-2 text-foreground-muted lowercase">
                                    <x-compass::enum-label :value="$package->section" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </details>
        @else
            <p class="text-sm text-foreground-muted">{{ __('compass::composer.no_composer_lock') }}</p>
        @endif
    </main>
</x-compass::layouts.app>
