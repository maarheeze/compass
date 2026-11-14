@props(['label'])

<button
    aria-label="{{ $label }}"
    {{ $attributes->merge(['type' => 'submit', 'class' => 'group/icon-button relative flex rounded-md bg-accent-muted p-2 text-foreground-muted enabled:cursor-pointer enabled:hover:bg-control-hover']) }}>
    {{ $slot }}
    <span
        aria-hidden="true"
        class="pointer-events-none absolute left-1/2 top-full z-10 mt-1 -translate-x-1/2 whitespace-nowrap rounded-md bg-foreground px-2 py-1 text-xs text-canvas opacity-0 group-hover/icon-button:opacity-100 group-focus-visible/icon-button:opacity-100">
        {{ $label }}
    </span>
</button>
