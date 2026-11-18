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
@if ($onlineSince !== null)
    <span>&middot;</span>
    <span>
        {{ __('compass::site.online_since') }}
        <x-compass::time :time="$onlineSince" />
    </span>
@endif
