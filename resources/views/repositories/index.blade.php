<x-compass::layouts.app :title="__('Repositories')">
    <main class="mx-auto max-w-5xl space-y-6 p-6">
        <x-compass::navigation />

        <section class="space-y-3">
            <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Projects</h2>
            <x-compass::repository-table :repositories="$projects" />
        </section>

        <section class="space-y-3">
            <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Libraries</h2>
            <x-compass::repository-table :repositories="$libraries" />
        </section>

        <section class="space-y-3">
            <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Other</h2>
            <x-compass::repository-table :repositories="$otherRepositories" />
        </section>
    </main>
</x-compass::layouts.app>
