@props(['title'])

<section {{ $attributes->merge(['class' => 'space-y-3 border-b border-border pb-4']) }}>
    <h2 class="text-xs font-semibold text-foreground-muted">{{ $title }}</h2>
    {{ $slot }}
</section>
