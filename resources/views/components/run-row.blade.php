@props(['run'])

<tr>
    <td class="px-4 py-3">
        <div class="flex min-w-0 items-center gap-2">
            <span class="text-base font-semibold text-foreground">
                <x-compass::enum-label :value="$run->type" />
            </span>
            <x-compass::run-status-badge :status="$run->status" />
        </div>
        <div class="mt-1 flex flex-wrap items-center gap-x-2 text-xs text-foreground-muted">
            <span class="font-mono">{{ $run->id }}</span>
            @if ($run->failed_job_count > 0)
                <span>&middot;</span>
                <span>{{ __('compass::run.failed_job_count', ['count' => $run->failed_job_count]) }}</span>
            @endif
            <span>&middot;</span>
            <span>
                {{ __('compass::run.started') }}
                <x-compass::date-time :time="$run->created_at" />
            </span>
            @if ($run->finished_at !== null)
                <span>&middot;</span>
                <span>
                    {{ __('compass::run.ended') }}
                    <x-compass::date-time :time="$run->finished_at" />
                </span>
            @endif
        </div>
    </td>
</tr>
