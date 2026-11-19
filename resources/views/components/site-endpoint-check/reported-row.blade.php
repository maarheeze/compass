@props(['site', 'siteEndpoint'])

<x-compass::site-endpoint-check.row
    :site="$site"
    :path="$siteEndpoint->path"
    :site-endpoint-check="$siteEndpoint->latestCheck">
    <x-slot:schedule>
        <span>&middot;</span>
        <span>{{ trans_choice('compass::site.frequency', $siteEndpoint->frequency_minutes) }}</span>
        <span>&middot;</span>
        <span>
            {{ __('compass::site.due') }}
            <x-compass::time :time="$siteEndpoint->due_at" />
        </span>
    </x-slot:schedule>
</x-compass::site-endpoint-check.row>
