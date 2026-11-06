@props(['person'])

<img
    src="{{ $person->avatarUrl() }}"
    alt="{{ $person->avatarName() }}"
    title="{{ $person->avatarName() }}"
    {{ $attributes->merge(['class' => 'max-w-none rounded-full border border-border']) }}>
