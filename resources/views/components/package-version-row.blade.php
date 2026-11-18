<tr>
    <td class="px-4 py-2">
        <div class="flex items-center justify-between gap-4">
            <a href="{{ $url }}" class="truncate font-mono text-foreground hover:text-accent">{{ $versionCount->version }}</a>
            <x-compass::badge type="neutral">{{ $versionCount->count }}</x-compass::badge>
        </div>
    </td>
</tr>
