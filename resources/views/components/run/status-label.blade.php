@props(['status'])

@switch ($status)
    @case (\Maarheeze\Compass\Collector\RunStatus::FAILED)
        <span class="rounded-md bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700 ring-1 ring-red-600/20 dark:bg-red-400/10 dark:text-red-400 dark:ring-red-400/20">{{ __('compass::run-status.FAILED') }}</span>
        @break
    @case (\Maarheeze\Compass\Collector\RunStatus::RUNNING)
        <span class="rounded-md bg-sky-50 px-2 py-0.5 text-xs font-medium text-sky-700 ring-1 ring-sky-600/20 dark:bg-sky-400/10 dark:text-sky-400 dark:ring-sky-400/20">{{ __('compass::run-status.RUNNING') }}</span>
        @break
    @case (\Maarheeze\Compass\Collector\RunStatus::SUCCEEDED)
        <span class="rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-400 dark:ring-emerald-400/20">{{ __('compass::run-status.SUCCEEDED') }}</span>
        @break
@endswitch
