<x-compass::layouts.app :title="__('Repositories')">
    <x-compass::navigation :section="\Maarheeze\Compass\View\NavigationSection::REPOSITORIES" />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <section class="space-y-3">
            <h2 class="text-sm font-semibold text-foreground">Projects</h2>
            <x-compass::repository-table :repositories="$projects" />
        </section>

        <section class="space-y-3">
            <h2 class="text-sm font-semibold text-foreground">Libraries</h2>
            <x-compass::repository-table :repositories="$libraries" />
        </section>

        <section class="space-y-3">
            <h2 class="text-sm font-semibold text-foreground">Other</h2>
            <x-compass::repository-table :repositories="$otherRepositories" />
        </section>
    </main>
</x-compass::layouts.app>
