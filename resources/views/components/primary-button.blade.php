<button
    {{ $attributes->merge(['type' => 'submit', 'class' => 'rounded-md bg-button-primary px-3 py-1.5 text-sm font-medium text-white enabled:cursor-pointer enabled:hover:bg-button-primary-hover disabled:cursor-not-allowed disabled:opacity-50']) }}>
    {{ $slot }}
</button>
