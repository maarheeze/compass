<tr>
    <td class="px-4 py-3">
        <x-compass::subject-name :subject="$repository">
            <a href="{{ $url }}" class="truncate text-base font-semibold text-foreground hover:text-accent">{{ $repository->name }}</a>
            <x-compass::badge type="neutral">
                <x-compass::enum-label :labelled-enum="$repository->visibility" />
            </x-compass::badge>
        </x-compass::subject-name>
        <div class="mt-1 flex flex-wrap items-center gap-x-2 text-xs text-foreground-muted">
            @if ($repository->composerJson?->php_constraint)
                <span>PHP {{ $repository->composerJson->php_constraint }}</span>
                <span>&middot;</span>
            @endif
            <span>{{ $repository->default_branch }}</span>
            @if ($repository->issues_count > 0)
                <span>&middot;</span>
                <span class="flex items-center gap-1">
                    <x-compass::icons.issue-opened class="size-3" />
                    {{ $repository->issues_count }}
                </span>
            @endif
            @if ($repository->pull_requests_count > 0)
                <span>&middot;</span>
                <span class="flex items-center gap-1">
                    <x-compass::icons.git-pull-request class="size-3" />
                    {{ $repository->pull_requests_count }}
                </span>
            @endif
            <span>&middot;</span>
            <span>
                {{ __('compass::repository.updated') }}
                <x-compass::time :time="$repository->last_commit_committed_at" />
            </span>
        </div>
    </td>
</tr>
