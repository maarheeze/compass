@props(['label'])

<div>
    <dt class="text-xs text-foreground-muted">{{ $label }}</dt>
    <dd {{ $attributes->merge(['class' => 'text-foreground']) }}>{{ $slot }}</dd>
</div>
