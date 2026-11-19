@props(['icon', 'title', 'counts' => null, 'rows' => null, 'moreRows' => null, 'empty' => null])

<div {{ $attributes->merge(['class' => 'group/list rounded-md border border-border bg-canvas [&:has(:checked):has(tbody:last-child>tr)>label]:rounded-b-none [&:has(tbody:first-child>tr)>label]:rounded-b-none']) }}>
    <label @class(['flex items-center gap-2 rounded-md bg-canvas-subtle px-4 py-3 text-sm', 'cursor-pointer' => $moreRows !== null])>
        @if ($moreRows !== null)
            <input type="checkbox" class="sr-only" data-compass-collapsible-toggle>
        @endif
        <x-compass::section-heading>
            <x-slot:icon>
                {{ $icon }}
            </x-slot:icon>
            {{ $title }}
        </x-compass::section-heading>
        {{ $counts }}
        @if ($moreRows !== null)
            <span class="ml-auto text-foreground-muted transition group-has-checked/list:rotate-90">&rsaquo;</span>
        @endif
    </label>

    <table class="w-full table-fixed text-left text-sm">
        <tbody class="*:border-t *:border-border">
            {{ $rows }}
            @if ($empty !== null && $rows?->hasActualContent() !== true && $moreRows?->hasActualContent() !== true)
                <tr>
                    <td class="px-4 py-3 text-foreground-muted">{{ $empty }}</td>
                </tr>
            @endif
        </tbody>
        @if ($moreRows !== null)
            <tbody class="hidden *:border-t *:border-border group-has-checked/list:table-row-group">
                {{ $moreRows }}
            </tbody>
        @endif
    </table>
</div>
