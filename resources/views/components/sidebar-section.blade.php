@props(['title'])

<section {{ $attributes->merge(['class' => 'space-y-3 border-b border-border pb-4']) }}>
    <div class="flex items-center justify-between gap-2">
        <h2 class="text-xs font-semibold text-foreground-muted">{{ $title }}</h2>
        @isset($action)
            {{ $action }}
        @endisset
    </div>
    {{ $slot }}
</section>
