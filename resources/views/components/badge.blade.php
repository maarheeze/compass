@props(['type'])

<span
    @class([
        'rounded-md px-2 py-0.5 text-xs font-medium ring-1',
        'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-400/10 dark:text-red-400 dark:ring-red-400/20' => $type === 'danger',
        'bg-sky-50 text-sky-700 ring-sky-600/20 dark:bg-sky-400/10 dark:text-sky-400 dark:ring-sky-400/20' => $type === 'info',
        'bg-border/50 text-foreground-muted ring-border' => $type === 'neutral',
        'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-400 dark:ring-emerald-400/20' => $type === 'success',
        'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-400 dark:ring-amber-400/20' => $type === 'warning',
    ])
>{{ $slot }}</span>
