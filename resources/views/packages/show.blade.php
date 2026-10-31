<x-compass::layouts.app :title="$packageName">
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <h1 class="text-lg font-semibold text-foreground">{{ $packageName }}</h1>

        <section class="space-y-3">
            <x-compass::section-heading>
                <x-slot:icon>
                    <x-compass::icons.repo />
                </x-slot:icon>
                {{ __('compass::package.projects') }}
            </x-compass::section-heading>

            @if ($packageUsages->lockedPackages->isEmpty())
                <p class="text-sm text-foreground-muted">{{ __('compass::package.no_locking_project') }}</p>
            @else
                <div class="overflow-hidden rounded-md border border-border bg-canvas">
                    <table class="w-full table-fixed text-left text-sm">
                        <tbody class="divide-y divide-border">
                            @foreach ($packageUsages->lockedPackages as $lockedPackage)
                                <x-compass::package-usage-row
                                    :repository="$lockedPackage->composerLock->repository"
                                    :version="$lockedPackage->version"
                                    :section="$lockedPackage->section" />
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="space-y-3">
            <x-compass::section-heading>
                <x-slot:icon>
                    <x-compass::icons.package />
                </x-slot:icon>
                {{ __('compass::package.libraries') }}
            </x-compass::section-heading>

            @if ($packageUsages->requiredPackages->isEmpty())
                <p class="text-sm text-foreground-muted">{{ __('compass::package.no_requiring_library') }}</p>
            @else
                <div class="overflow-hidden rounded-md border border-border bg-canvas">
                    <table class="w-full table-fixed text-left text-sm">
                        <tbody class="divide-y divide-border">
                            @foreach ($packageUsages->requiredPackages as $requiredPackage)
                                <x-compass::package-usage-row
                                    :repository="$requiredPackage->composerJson->repository"
                                    :version="$requiredPackage->constraint"
                                    :section="$requiredPackage->section" />
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </main>
</x-compass::layouts.app>
