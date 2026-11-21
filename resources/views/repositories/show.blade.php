<x-compass::layouts.app>
    <x-slot:title>{{ $repository->name }}</x-slot:title>
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <x-compass::repository-missing-notice :repository="$repository" />

        <x-compass::repository-title :repository="$repository">
            <h1 class="text-lg font-semibold text-foreground">{{ $repository->name }}</h1>
        </x-compass::repository-title>

        <x-compass::page-columns>
            <x-compass::alert-table :alerts="$alerts" data-compass-live-region="alerts" />

            <x-compass::alert-check-table :alert-checks="$alertChecks" />

            <x-compass::issue-list :issues="$issues" />

            <x-compass::pull-request-list :pull-requests="$pullRequests" />

            <x-compass::site-list :sites="$sites" />

            @if ($repository->composerJson)
                <x-compass::list>
                    <x-slot:icon>
                        <x-compass::icons.file-code />
                    </x-slot:icon>
                    <x-slot:title>composer.json</x-slot:title>
                    <x-slot:counts>
                        @if ($repository->composerJson->type)
                            <span class="text-foreground-muted">{{ $repository->composerJson->type }}</span>
                        @endif
                        @if ($productionPackages->isNotEmpty())
                            <x-compass::badge type="neutral">{{ __('compass::composer.production_count', ['count' => $productionPackages->count()]) }}</x-compass::badge>
                        @endif
                        @if ($developmentPackages->isNotEmpty())
                            <x-compass::badge type="neutral">{{ __('compass::composer.development_count', ['count' => $developmentPackages->count()]) }}</x-compass::badge>
                        @endif
                    </x-slot:counts>
                    <x-slot:more-rows>
                        @if ($productionPackages->isNotEmpty())
                            <tr>
                                <th class="bg-canvas-subtle px-4 py-2 text-xs font-medium text-foreground-muted">{{ __('compass::composer.production') }}</th>
                            </tr>
                            @foreach ($productionPackages as $package)
                                <x-compass::composer.package-row :package-name="$package->name">
                                    <span class="font-mono">{{ $package->constraint }}</span>
                                </x-compass::composer.package-row>
                            @endforeach
                        @endif
                        @if ($developmentPackages->isNotEmpty())
                            <tr>
                                <th class="bg-canvas-subtle px-4 py-2 text-xs font-medium text-foreground-muted">{{ __('compass::composer.development') }}</th>
                            </tr>
                            @foreach ($developmentPackages as $package)
                                <x-compass::composer.package-row :package-name="$package->name">
                                    <span class="font-mono">{{ $package->constraint }}</span>
                                </x-compass::composer.package-row>
                            @endforeach
                        @endif
                    </x-slot:more-rows>
                </x-compass::list>
            @else
                <x-compass::list>
                    <x-slot:icon>
                        <x-compass::icons.file-code />
                    </x-slot:icon>
                    <x-slot:title>composer.json</x-slot:title>
                    <x-slot:empty>{{ __('compass::composer.composer_json_none') }}</x-slot:empty>
                </x-compass::list>
            @endif

            @if ($repository->composerLock)
                <x-compass::list>
                    <x-slot:icon>
                        <x-compass::icons.file-code />
                    </x-slot:icon>
                    <x-slot:title>composer.lock</x-slot:title>
                    <x-slot:counts>
                        @if ($repository->composerLock->php_constraint)
                            <span class="text-foreground-muted">PHP {{ $repository->composerLock->php_constraint }}</span>
                        @endif
                        <x-compass::badge type="neutral">{{ __('compass::composer.package_count', ['count' => $lockedPackages->count()]) }}</x-compass::badge>
                    </x-slot:counts>
                    <x-slot:more-rows>
                        @foreach ($lockedPackages as $package)
                            <x-compass::composer.package-row :package-name="$package->name">
                                <span class="font-mono">{{ $package->version }}</span>
                                <span>&middot;</span>
                                <span class="lowercase">
                                    <x-compass::enum-label :labelled-enum="$package->section" />
                                </span>
                            </x-compass::composer.package-row>
                        @endforeach
                    </x-slot:more-rows>
                </x-compass::list>
            @else
                <x-compass::list>
                    <x-slot:icon>
                        <x-compass::icons.file-code />
                    </x-slot:icon>
                    <x-slot:title>composer.lock</x-slot:title>
                    <x-slot:empty>{{ __('compass::composer.composer_lock_none') }}</x-slot:empty>
                </x-compass::list>
            @endif

            <x-slot:aside>
                <x-compass::sidebar-section :title="__('compass::repository.about')">
                    <dl class="space-y-2">
                        <x-compass::fact :label="__('compass::repository.kind')">
                            <x-compass::enum-label :labelled-enum="$kind" />
                        </x-compass::fact>
                        <x-compass::fact :label="__('compass::repository.visibility')">
                            <x-compass::enum-label :labelled-enum="$repository->visibility" />
                        </x-compass::fact>
                        <x-compass::fact :label="__('compass::repository.default_branch')">{{ $repository->default_branch }}</x-compass::fact>
                        @if ($repository->composerJson?->php_constraint)
                            <x-compass::fact :label="__('compass::repository.php')">{{ $repository->composerJson->php_constraint }}</x-compass::fact>
                        @endif
                    </dl>
                </x-compass::sidebar-section>

                <p class="text-xs text-foreground-muted">
                    {{ __('compass::repository.collected') }}
                    <x-compass::time :time="$repository->collected_at" />
                </p>
            </x-slot:aside>
        </x-compass::page-columns>
    </main>
</x-compass::layouts.app>
