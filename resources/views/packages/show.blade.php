<x-compass::layouts.app :title="$packageName">
    <x-compass::navigation :section="\Maarheeze\Compass\View\NavigationSection::REPOSITORIES" />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <h1 class="text-lg font-semibold text-foreground">{{ $packageName }}</h1>

        <section class="space-y-3">
            <x-compass::section-heading>
                <x-slot:icon>
                    <x-compass::icons.repo />
                </x-slot:icon>
                Projects
            </x-compass::section-heading>

            @if ($packageUsages->lockedPackages->isEmpty())
                <p class="text-sm text-foreground-muted">No project has this package locked.</p>
            @else
                <div class="overflow-hidden rounded-md border border-border bg-canvas">
                    <table class="w-full table-fixed text-left text-sm">
                        <thead class="bg-canvas-subtle text-xs text-foreground-muted">
                            <tr>
                                <th class="px-4 py-2 font-medium">Repository</th>
                                <th class="w-40 px-4 py-2 font-medium">Version</th>
                                <th class="w-32 px-4 py-2 font-medium">Section</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach ($packageUsages->lockedPackages as $lockedPackage)
                                <tr>
                                    <td class="truncate px-4 py-2 text-foreground">
                                        <a href="{{ route(\Maarheeze\Compass\Http\RouteName::GITHUB_REPOSITORY, $lockedPackage->composerLock->repository) }}" class="text-accent hover:underline">{{ $lockedPackage->composerLock->repository->name }}</a>
                                    </td>
                                    <td class="px-4 py-2 text-foreground-muted">{{ $lockedPackage->version }}</td>
                                    <td class="px-4 py-2 text-foreground-muted lowercase">{{ $lockedPackage->section->value }}</td>
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
                Libraries
            </x-compass::section-heading>

            @if ($packageUsages->requiredPackages->isEmpty())
                <p class="text-sm text-foreground-muted">No library requires this package.</p>
            @else
                <div class="overflow-hidden rounded-md border border-border bg-canvas">
                    <table class="w-full table-fixed text-left text-sm">
                        <thead class="bg-canvas-subtle text-xs text-foreground-muted">
                            <tr>
                                <th class="px-4 py-2 font-medium">Repository</th>
                                <th class="w-40 px-4 py-2 font-medium">Constraint</th>
                                <th class="w-32 px-4 py-2 font-medium">Section</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach ($packageUsages->requiredPackages as $requiredPackage)
                                <tr>
                                    <td class="truncate px-4 py-2 text-foreground">
                                        <a href="{{ route(\Maarheeze\Compass\Http\RouteName::GITHUB_REPOSITORY, $requiredPackage->composerJson->repository) }}" class="text-accent hover:underline">{{ $requiredPackage->composerJson->repository->name }}</a>
                                    </td>
                                    <td class="px-4 py-2 text-foreground-muted">{{ $requiredPackage->constraint }}</td>
                                    <td class="px-4 py-2 text-foreground-muted lowercase">{{ $requiredPackage->section->value }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </main>
</x-compass::layouts.app>
