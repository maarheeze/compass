@props(['pullRequest'])

<tr>
    <td class="px-4 py-3">
        <div class="flex gap-2">
            <x-compass::icons.git-pull-request
                @class([
                    'mt-1 size-4 shrink-0',
                    'text-foreground-muted' => $pullRequest->is_draft,
                    'text-open' => !$pullRequest->is_draft,
                ]) />
            <div class="min-w-0">
                <a href="{{ $pullRequest->url }}" target="_blank" rel="noopener" class="text-base font-semibold text-foreground hover:text-accent">{{ $pullRequest->title }}</a>
                <div class="mt-0.5 text-xs text-foreground-muted">
                    <x-compass::issue-reference :repository="$pullRequest->repository" :number="$pullRequest->number" />
                    ·
                    @if ($pullRequest->author_login !== null)
                        {{ $pullRequest->author_login }}
                    @endif
                    {{ __('compass::pull-request.opened') }}
                    <x-compass::time :time="$pullRequest->opened_at" />
                    @if ($pullRequest->is_draft)
                        ·
                        {{ __('compass::pull-request.draft') }}
                    @endif
                </div>
            </div>
        </div>
    </td>
    <td class="w-32 px-4 py-3">
        <div class="flex justify-end -space-x-1">
            @foreach ($pullRequest->assignees as $assignee)
                <x-compass::assignee-avatar :assignee="$assignee" class="size-5" />
            @endforeach
        </div>
    </td>
</tr>
