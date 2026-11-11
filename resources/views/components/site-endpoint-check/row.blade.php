@props(['site', 'siteEndpointCheck'])

<tr>
    <td class="px-4 py-3">
        <div class="flex min-w-0 items-center gap-2">
            <x-compass::site-endpoint-checks-link :site="$site" :path="$siteEndpointCheck->path" />
            <x-compass::site-endpoint-check.status-label :site-endpoint-check="$siteEndpointCheck" />
            {{ $slot }}
        </div>
        <div class="mt-1 flex flex-wrap items-center gap-x-2 text-xs text-foreground-muted">
            <span>
                @if ($siteEndpointCheck->status_code === null)
                    {{ __('compass::endpoint-check.unreachable') }}
                @else
                    {{ $siteEndpointCheck->status_code }}
                @endif
            </span>
            <span>&middot;</span>
            <span>
                {{ __('compass::site.checked') }}
                <x-compass::time :time="$siteEndpointCheck->created_at" />
            </span>
            @if ($siteEndpointCheck->is_successful)
                <span>&middot;</span>
                <span>
                    {{ __('compass::site.online_since') }}
                    <x-compass::time :time="$siteEndpointCheck->online_since" />
                </span>
            @endif
        </div>
    </td>
</tr>
