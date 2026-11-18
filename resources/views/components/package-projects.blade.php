@props(['lockedPackages'])

<section class="space-y-3">
    <x-compass::section-heading>
        <x-slot:icon>
            <x-compass::icons.repo />
        </x-slot:icon>
        {{ __('compass::package.projects') }}
    </x-compass::section-heading>

    @if ($lockedPackages->isEmpty())
        <p class="text-sm text-foreground-muted">{{ __('compass::package.no_locking_project') }}</p>
    @else
        <div class="overflow-hidden rounded-md border border-border bg-canvas">
            <table class="w-full table-fixed text-left text-sm">
                <tbody class="divide-y divide-border">
                    @foreach ($lockedPackages as $lockedPackage)
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
