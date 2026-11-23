<tr>
    <td class="px-4 py-3">
        <div class="flex gap-2">
            <x-compass::icons.issue-opened class="mt-1 size-4 shrink-0 text-open" />
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <x-compass::source-link :source-url="$issue->source_url" class="text-base font-semibold text-foreground">{{ $issue->title }}</x-compass::source-link>
                    @if ($issue->type !== null)
                        <x-compass::issue-type-badge :issue-type="$issue->type" />
                    @endif
                </div>
                <div class="mt-0.5 text-xs text-foreground-muted">
                    <x-compass::issue-reference :external-reference="$issue->external_reference" :repository="$issue->repository" />
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
