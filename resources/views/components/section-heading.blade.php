@props(['icon'])

<h2 class="flex items-center gap-2 text-sm font-semibold text-foreground">
    <span class="flex text-foreground-muted">{{ $icon }}</span>
    {{ $slot }}
</h2>
