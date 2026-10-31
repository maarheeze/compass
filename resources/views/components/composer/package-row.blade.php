<tr>
    <td class="px-4 py-2">
        <div class="flex items-center justify-between gap-4">
            <a href="{{ $url }}" class="truncate text-foreground hover:text-accent">{{ $packageName }}</a>
            <div class="flex shrink-0 items-center gap-x-2 text-foreground-muted">
                {{ $slot }}
            </div>
        </div>
    </td>
</tr>
