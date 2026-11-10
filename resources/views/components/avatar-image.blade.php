@props(['imageUrl', 'name'])

<img
    src="{{ $imageUrl }}"
    alt="{{ $name }}"
    title="{{ $name }}"
    {{ $attributes->merge(['class' => 'max-w-none rounded-full border border-border']) }}>
