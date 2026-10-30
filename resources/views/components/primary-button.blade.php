<button
    {{ $attributes->merge(['type' => 'submit', 'class' => 'rounded-md bg-green-700 px-3 py-1.5 text-sm font-medium text-white enabled:cursor-pointer enabled:hover:bg-green-800 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-green-600 dark:enabled:hover:bg-green-700']) }}>
    {{ $slot }}
</button>
