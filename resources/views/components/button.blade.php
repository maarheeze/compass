<button
    {{ $attributes->merge(['type' => 'submit', 'class' => 'rounded-md border border-border bg-canvas-subtle px-3 py-1.5 text-sm font-medium text-foreground enabled:cursor-pointer enabled:hover:bg-border/30 disabled:cursor-not-allowed disabled:opacity-50']) }}>
    {{ $slot }}
</button>
