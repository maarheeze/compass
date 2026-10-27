@props(['icon', 'link', 'text'])

<li class="flex items-center gap-4 px-4 py-2 text-sm">
    <span class="flex text-foreground-muted">{{ $icon }}</span>
    {{ $link }}
    <span class="text-foreground-muted">{{ $text }}</span>
    <span class="ml-auto flex items-center gap-4">{{ $slot }}</span>
</li>
