<x-compass::layouts.app :title="__('Repositories')">
    <x-compass::navigation :section="\Maarheeze\Compass\View\NavigationSection::REPOSITORIES" />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <section class="space-y-3">
            <x-compass::section-heading>
                <x-slot:icon>
                    <x-compass::icons.repo />
                </x-slot:icon>
                Projects
            </x-compass::section-heading>
            <x-compass::repository-table :repositories="$projects" />
        </section>

        <section class="space-y-3">
            <x-compass::section-heading>
                <x-slot:icon>
                    <x-compass::icons.package />
                </x-slot:icon>
                Libraries
            </x-compass::section-heading>
            <x-compass::repository-table :repositories="$libraries" />
        </section>

        <section class="space-y-3">
            <x-compass::section-heading>
                <x-slot:icon>
                    <x-compass::icons.file-directory />
                </x-slot:icon>
                Other
            </x-compass::section-heading>
            <x-compass::repository-table :repositories="$otherRepositories" />
        </section>
    </main>
</x-compass::layouts.app>
