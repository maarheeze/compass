@props(['site', 'path', 'siteEndpointCheck', 'schedule' => null])

<tr>
    <td class="px-4 py-3">
        <div class="flex min-w-0 items-center gap-2">
            <x-compass::site-endpoint-checks-link :site="$site" :path="$path" />
            @if ($siteEndpointCheck !== null)
                <x-compass::site-endpoint-check.status-label :site-endpoint-check="$siteEndpointCheck" />
            @endif
            {{ $slot }}
        </div>
        <div class="mt-1 flex flex-wrap items-center gap-x-2 text-xs text-foreground-muted">
            @if ($siteEndpointCheck === null)
                <span>{{ __('compass::site.not_checked_yet') }}</span>
            @else
                <x-compass::site-endpoint-check.details :site-endpoint-check="$siteEndpointCheck" />
            @endif
            {{ $schedule }}
        </div>
    </td>
</tr>
