<tr>
    <td class="px-4 py-3">
        <a href="{{ $url }}" class="truncate text-base font-semibold text-foreground hover:text-accent">{{ $repository->name }}</a>
        <div class="mt-1 flex flex-wrap items-center gap-x-2 text-xs text-foreground-muted">
            <span class="font-mono">{{ $version }}</span>
            <span>&middot;</span>
            <span class="lowercase">
                <x-compass::enum-label :labelled-enum="$section" />
            </span>
        </div>
    </td>
</tr>
