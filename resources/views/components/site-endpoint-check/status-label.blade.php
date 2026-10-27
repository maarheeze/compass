@props(['siteEndpointCheck'])

@if ($siteEndpointCheck->is_successful)
    <span class="rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-400 dark:ring-emerald-400/20">{{ __('compass::endpoint-check.ok') }}</span>
@else
    <span class="rounded-md bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700 ring-1 ring-red-600/20 dark:bg-red-400/10 dark:text-red-400 dark:ring-red-400/20">{{ __('compass::endpoint-check.not_ok') }}</span>
@endif
