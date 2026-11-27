<x-compass::list :attributes="$attributes">
    <x-slot:icon>
        <x-compass::icons.history />
    </x-slot:icon>
    <x-slot:title>{{ __('compass::site.scheduled_tasks') }}</x-slot:title>
    <x-slot:empty>{{ __('compass::site.scheduled_tasks_none') }}</x-slot:empty>
    <x-slot:counts>
        @if ($scheduledTasks->isNotEmpty())
            <x-compass::badge type="neutral">{{ trans_choice('compass::site.scheduled_task_count', $scheduledTasks->count()) }}</x-compass::badge>
        @endif
    </x-slot:counts>
    <x-slot:more-rows>
        @foreach ($scheduledTasks as $scheduledTask)
            <tr>
                <td class="truncate px-4 py-2 font-mono" title="{{ $scheduledTask->command }}">{{ $scheduledTask->displayedCommand() }}</td>
                <td class="w-48 px-4 py-2 text-right text-foreground-muted" title="{{ $scheduledTask->expression }}">{{ $timing($scheduledTask) }}</td>
            </tr>
        @endforeach
    </x-slot:more-rows>
</x-compass::list>
