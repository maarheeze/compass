<x-compass::fact :label="__('compass::site.scheduler')">
    <x-compass::badge :type="$badgeType->value">
        <x-compass::enum-label :labelled-enum="$schedulerHealth" />
    </x-compass::badge>
    @if ($scheduledTaskCount !== null)
        <span class="text-foreground-muted">
            &middot;
            @if ($siteReport->scheduler_last_ran_at !== null)
                {{ __('compass::site.last_ran') }} <x-compass::time :time="$siteReport->scheduler_last_ran_at" />
            @else
                {{ __('compass::site.never_ran') }}
            @endif
            &middot; {{ trans_choice('compass::site.scheduled_task_count', $scheduledTaskCount) }}
        </span>
    @endif
</x-compass::fact>
