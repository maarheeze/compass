<x-compass::layouts.app>
    <x-slot:title>{{ __('compass::navigation.repositories') }}</x-slot:title>
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <section class="space-y-3">
            <x-compass::section-heading>
                <x-slot:icon>
                    <x-compass::icons.repo />
                </x-slot:icon>
                {{ __('compass::repository.projects') }}
            </x-compass::section-heading>
            <x-compass::repository-table :repositories="$projects" />
        </section>

        <section class="space-y-3">
            <x-compass::section-heading>
                <x-slot:icon>
                    <x-compass::icons.package />
                </x-slot:icon>
                {{ __('compass::repository.libraries') }}
            </x-compass::section-heading>
            <x-compass::repository-table :repositories="$libraries" />
        </section>

        <section class="space-y-3">
            <x-compass::section-heading>
                <x-slot:icon>
                    <x-compass::icons.file-directory />
                </x-slot:icon>
                {{ __('compass::repository.other') }}
            </x-compass::section-heading>
            <x-compass::repository-table :repositories="$otherRepositories" />
        </section>
    </main>
</x-compass::layouts.app>
