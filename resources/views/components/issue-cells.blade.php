@props(['issue'])

<td class="truncate px-4 py-2 text-foreground">
    <a href="{{ $issue->url }}" target="_blank" rel="noopener" class="text-accent hover:underline">
        {{ $issue->title }}
        <span class="text-foreground-muted">#{{ $issue->number }}</span>
        &nearr;
    </a>
</td>
<td class="truncate px-4 py-2 text-foreground-muted">
    @foreach ($issue->assignees as $assignee)
        <span>{{ $assignee->login }}</span>
    @endforeach
</td>
<td class="px-4 py-2 text-foreground-muted">
    <x-compass::time :time="$issue->opened_at" />
</td>
