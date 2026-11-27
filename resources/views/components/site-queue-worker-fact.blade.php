<x-compass::fact :label="__('compass::site.queue_worker')">
    <x-compass::badge :type="$badgeType->value">
        <x-compass::enum-label :labelled-enum="$queueWorkerHealth" />
    </x-compass::badge>
    @if ($notRunningQueueWorkerState !== null)
        <span class="text-foreground-muted">&middot; <x-compass::enum-label :labelled-enum="$notRunningQueueWorkerState" /></span>
    @endif
    @if ($collectedAt !== null)
        <span class="text-foreground-muted">&middot; <x-compass::time :time="$collectedAt" /></span>
    @endif
</x-compass::fact>
