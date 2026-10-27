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
                        <thead class="bg-canvas-subtle text-xs text-foreground-muted">
                            <tr>
                                <th class="px-4 py-2 font-medium">{{ __('compass::repository.repository') }}</th>
                                <th class="w-40 px-4 py-2 font-medium">{{ __('compass::composer.version') }}</th>
                                <th class="w-32 px-4 py-2 font-medium">{{ __('compass::composer.section') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach ($packageUsages->lockedPackages as $lockedPackage)
                                <tr>
                                    <td class="truncate px-4 py-2 text-foreground">
                                        <x-compass::repository-link :repository="$lockedPackage->composerLock->repository" />
                                    </td>
                                    <td class="px-4 py-2 text-foreground-muted">{{ $lockedPackage->version }}</td>
                                    <td class="px-4 py-2 text-foreground-muted lowercase">
                                        <x-compass::enum-label :value="$lockedPackage->section" />
                                    </td>
                                </tr>
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
                        <thead class="bg-canvas-subtle text-xs text-foreground-muted">
                            <tr>
                                <th class="px-4 py-2 font-medium">{{ __('compass::repository.repository') }}</th>
                                <th class="w-40 px-4 py-2 font-medium">{{ __('compass::composer.constraint') }}</th>
                                <th class="w-32 px-4 py-2 font-medium">{{ __('compass::composer.section') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach ($packageUsages->requiredPackages as $requiredPackage)
                                <tr>
                                    <td class="truncate px-4 py-2 text-foreground">
                                        <x-compass::repository-link :repository="$requiredPackage->composerJson->repository" />
                                    </td>
                                    <td class="px-4 py-2 text-foreground-muted">{{ $requiredPackage->constraint }}</td>
                                    <td class="px-4 py-2 text-foreground-muted lowercase">
                                        <x-compass::enum-label :value="$requiredPackage->section" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </main>
</x-compass::layouts.app>
