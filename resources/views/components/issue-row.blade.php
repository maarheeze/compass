<tr>
    <td class="px-4 py-3">
        <div class="flex gap-2">
            <x-compass::icons.issue-opened class="mt-1 size-4 shrink-0 text-open" />
            <div class="min-w-0">
                <a href="{{ $issue->url }}" target="_blank" rel="noopener" class="text-base font-semibold text-foreground hover:text-accent">{{ $issue->title }}</a>
                <div class="mt-0.5 text-xs text-foreground-muted">
                    <x-compass::issue-reference :number="$issue->number" :repository="$issue->repository" />
                    ·
                    @if ($issue->author_login !== null)
                        {{ $issue->author_login }}
                    @endif
                    {{ __('compass::issue.opened') }}
                    <x-compass::time :time="$issue->opened_at" />
                </div>
            </div>
        </div>
    </td>
    <td class="w-32 px-4 py-3">
        <div class="flex justify-end -space-x-1">
            @foreach ($issue->assignees as $assignee)
                <x-compass::avatar
                    :name="$assignee->login"
                    :image-url="$assignee->avatar_url"
                    :url="$assigneeUrl($assignee)"
                    class="size-5"
                />
            @endforeach
        </div>
    </td>
</tr>
