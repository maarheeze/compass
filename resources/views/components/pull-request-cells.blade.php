@props(['pullRequest'])

<td class="truncate px-4 py-2 text-foreground">
    @if ($pullRequest->is_draft)
        <span class="mr-1 rounded bg-canvas-subtle px-1.5 py-0.5 text-xs text-foreground-muted">Draft</span>
    @endif
    <a href="{{ $pullRequest->url }}" target="_blank" rel="noopener" class="text-accent hover:underline">
        {{ $pullRequest->title }}
        <span class="text-foreground-muted">#{{ $pullRequest->number }}</span>
        &nearr;
    </a>
</td>
<td class="truncate px-4 py-2 text-foreground-muted">
    @foreach ($pullRequest->assignees as $assignee)
        <span>{{ $assignee->login }}</span>
    @endforeach
</td>
<td class="px-4 py-2 text-foreground-muted">
    <x-compass::time :time="$pullRequest->opened_at" />
</td>
