<button
    {{ $attributes->merge(['type' => 'submit', 'class' => 'cursor-pointer rounded-md border border-border bg-canvas-subtle px-3 py-1.5 text-sm font-medium text-foreground hover:bg-border/30']) }}>
    {{ $slot }}
</button>
