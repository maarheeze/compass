<x-compass::layouts.app :title="__('Dashboard')">
    <main class="mx-auto max-w-5xl space-y-6 p-6">
        <x-compass::navigation :user="$user" />

        @if ($repositoriesWithComposerWarning->isEmpty())
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Nothing needs attention.</p>
        @else
            <section class="space-y-3">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Composer warnings</h2>

                <ul class="divide-y divide-zinc-100 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-zinc-950/5 dark:divide-white/5 dark:bg-zinc-900 dark:ring-white/10">
                    @foreach ($repositoriesWithComposerWarning as $repository)
                        <li class="flex items-center justify-between px-4 py-2 text-sm">
                            <a href="{{ route(\Maarheeze\Compass\Http\RouteName::GITHUB_REPOSITORY, $repository) }}" class="font-medium text-zinc-900 hover:underline dark:text-zinc-100">{{ $repository->name }}</a>
                            <x-compass::composer-warning-badge :repository="$repository" />
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </main>
</x-compass::layouts.app>
