@props(['type'])

<span
    @class([
        'whitespace-nowrap rounded-full border px-2 text-xs font-medium',
        'border-red-600/40 text-red-700 dark:border-red-400/40 dark:text-red-400' => $type === 'danger',
        'border-sky-600/40 text-sky-700 dark:border-sky-400/40 dark:text-sky-400' => $type === 'info',
        'border-border text-foreground-muted' => $type === 'neutral',
        'border-open/40 text-open' => $type === 'success',
        'border-amber-600/40 text-amber-700 dark:border-amber-400/40 dark:text-amber-400' => $type === 'warning',
    ])
>{{ $slot }}</span>
