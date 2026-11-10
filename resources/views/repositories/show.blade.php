<x-compass::layouts.app>
    <x-slot:title>{{ $repository->name }}</x-slot:title>
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <x-compass::repository-missing-notice :repository="$repository" />

        <div class="space-y-1">
            <div class="flex items-center gap-3">
                <x-compass::subject-name :subject="$repository">
                    <h1 class="text-lg font-semibold text-foreground">{{ $repository->name }}</h1>
                </x-compass::subject-name>
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
                    <tbody class="divide-y divide-border">
                        @forelse ($repository->issues as $issue)
                            <x-compass::issue-row :issue="$issue" />
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-6 text-center text-foreground-muted">{{ __('compass::issue.no_open_issues') }}</td>
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
                    <tbody class="divide-y divide-border">
                        @forelse ($repository->pullRequests as $pullRequest)
                            <x-compass::pull-request-row :pull-request="$pullRequest" />
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-6 text-center text-foreground-muted">{{ __('compass::pull-request.no_open_pull_requests') }}</td>
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
                    <tbody class="divide-y divide-border">
                        @forelse ($repository->sites as $site)
                            <x-compass::site-row :site="$site" />
                        @empty
                            <tr>
                                <td class="px-4 py-6 text-center text-foreground-muted">{{ __('compass::repository.no_sites') }}</td>
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
                    @if ($repository->composerJson->productionPackages->isNotEmpty())
                        <tbody class="divide-y divide-border">
                            <tr>
                                <th class="bg-canvas-subtle px-4 py-2 text-xs font-medium text-foreground-muted">{{ __('compass::composer.production') }}</th>
                            </tr>
                            @foreach ($repository->composerJson->productionPackages as $package)
                                <x-compass::composer.package-row :package-name="$package->name">
                                    <span class="font-mono">{{ $package->constraint }}</span>
                                </x-compass::composer.package-row>
                            @endforeach
                        </tbody>
                    @endif
                    @if ($repository->composerJson->developmentPackages->isNotEmpty())
                        <tbody class="divide-y divide-border border-t border-border">
                            <tr>
                                <th class="bg-canvas-subtle px-4 py-2 text-xs font-medium text-foreground-muted">{{ __('compass::composer.development') }}</th>
                            </tr>
                            @foreach ($repository->composerJson->developmentPackages as $package)
                                <x-compass::composer.package-row :package-name="$package->name">
                                    <span class="font-mono">{{ $package->constraint }}</span>
                                </x-compass::composer.package-row>
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
                    <tbody class="divide-y divide-border">
                        @foreach ($repository->composerLock->packages as $package)
                            <x-compass::composer.package-row :package-name="$package->name">
                                <span class="font-mono">{{ $package->version }}</span>
                                <span>&middot;</span>
                                <span class="lowercase">
                                    <x-compass::enum-label :value="$package->section" />
                                </span>
                            </x-compass::composer.package-row>
                        @endforeach
                    </tbody>
                </table>
            </details>
        @else
            <p class="text-sm text-foreground-muted">{{ __('compass::composer.no_composer_lock') }}</p>
        @endif
    </main>
</x-compass::layouts.app>
