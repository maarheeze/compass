@props(['scheduledCommand'])

<li class="flex gap-2">
    <x-compass::icons.sync class="mt-0.5 size-4 shrink-0 text-foreground-muted" />
    <div class="min-w-0">
        <div class="break-words font-mono text-foreground">{{ $scheduledCommand->command }}</div>
        <div class="flex flex-wrap items-center gap-x-2 text-xs text-foreground-muted">
            <span>
                {{ __('compass::run.next_run') }}
                <x-compass::date-time :time="$scheduledCommand->nextRunAt" />
            </span>
            <span>&middot;</span>
            <x-compass::run-scheduled-command-button :scheduled-command="$scheduledCommand" />
        </div>
    </div>
</li>
