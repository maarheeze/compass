@props(['title', 'assignedRouteName', 'assignedCount', 'openRouteName', 'openCount'])

<section class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-zinc-950/5 dark:bg-zinc-900 dark:ring-white/10">
    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $title }}</h2>

    <div class="mt-3 grid grid-cols-2 gap-4">
        <a href="{{ route($assignedRouteName) }}" class="group">
            <p class="text-2xl font-semibold text-zinc-900 group-hover:underline dark:text-zinc-100">{{ $assignedCount }}</p>
            <p class="text-xs text-zinc-500 dark:text-zinc-400">Assigned to me</p>
        </a>
        <a href="{{ route($openRouteName) }}" class="group">
            <p class="text-2xl font-semibold text-zinc-900 group-hover:underline dark:text-zinc-100">{{ $openCount }}</p>
            <p class="text-xs text-zinc-500 dark:text-zinc-400">Open</p>
        </a>
    </div>
</section>
