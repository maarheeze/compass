<button
    {{ $attributes->merge(['type' => 'submit', 'class' => 'rounded-md bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 shadow-sm ring-1 ring-zinc-950/10 hover:bg-zinc-50 dark:bg-zinc-900 dark:text-zinc-200 dark:ring-white/10 dark:hover:bg-zinc-800']) }}>
    {{ $slot }}
</button>
