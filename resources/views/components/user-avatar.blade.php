<img
    src="{{ $user->avatar_url }}"
    alt="{{ $user->nickname }}"
    title="{{ $user->nickname }}"
    {{ $attributes->merge(['class' => 'max-w-none rounded-full border border-border']) }}>
