@props(['scheduledCommand'])

<tr>
    <td class="px-4 py-3">
        <div class="flex items-center justify-between gap-4">
            <div class="min-w-0">
                <code class="truncate text-sm font-semibold text-foreground">{{ $scheduledCommand->command }}</code>
                <div class="mt-1 text-xs text-foreground-muted">
                    {{ __('compass::run.next_run') }}
                    <x-compass::date-time :time="$scheduledCommand->nextRunAt" />
                </div>
            </div>
            <x-compass::run-scheduled-command-button :scheduled-command="$scheduledCommand" />
        </div>
    </td>
</tr>
