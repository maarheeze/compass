@props(['type'])

<div
    role="status"
    data-compass-toast
    {{ $attributes }}
    @class([
        'pointer-events-auto flex items-start justify-between gap-3 rounded-md border bg-canvas px-4 py-3 text-sm shadow-lg',
        'border-red-600/40 text-red-700 dark:border-red-400/40 dark:text-red-400' => $type === 'danger',
        'border-border text-foreground' => $type === 'neutral',
        'border-open/40 text-open' => $type === 'success',
    ])
>
    <span>
        {{ $slot }}
    </span>

    <button
        type="button"
        data-compass-toast-close
        aria-label="{{ __('compass::toast.close') }}"
        class="cursor-pointer text-foreground-muted hover:text-foreground">
        <x-compass::icons.x />
    </button>
</div>
