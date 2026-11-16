@props(['type'])

<div
    @class([
        'flex flex-wrap items-center gap-3 rounded-md px-4 py-3 text-sm ring-1',
        'bg-sky-50 text-sky-700 ring-sky-600/20 dark:bg-sky-400/10 dark:text-sky-400 dark:ring-sky-400/20' => $type === 'info',
        'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-400 dark:ring-amber-400/20' => $type === 'warning',
    ])
>
    {{ $slot }}
</div>
