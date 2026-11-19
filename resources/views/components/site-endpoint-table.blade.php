<x-compass::list :attributes="$attributes">
    <x-slot:icon>
        <x-compass::icons.eye />
    </x-slot:icon>
    <x-slot:title>{{ __('compass::site.endpoints') }}</x-slot:title>
    <x-slot:empty>{{ __('compass::site.endpoints_none') }}</x-slot:empty>
    <x-slot:counts>
        @if ($downSiteEndpointCount > 0)
            <x-compass::badge type="danger">{{ __('compass::site.endpoints_down', ['count' => $downSiteEndpointCount]) }}</x-compass::badge>
        @endif
        @if ($upSiteEndpointCount > 0)
            <x-compass::badge type="success">{{ __('compass::site.endpoints_up', ['count' => $upSiteEndpointCount]) }}</x-compass::badge>
        @endif
    </x-slot:counts>
    <x-slot:rows>
        @foreach ($downSiteEndpoints as $downSiteEndpoint)
            <x-compass::site-endpoint-check.reported-row :site="$site" :site-endpoint="$downSiteEndpoint" />
        @endforeach
    </x-slot:rows>
    <x-slot:more-rows>
        @foreach ($notDownSiteEndpoints as $notDownSiteEndpoint)
            <x-compass::site-endpoint-check.reported-row :site="$site" :site-endpoint="$notDownSiteEndpoint" />
        @endforeach
        @foreach ($inactiveSiteEndpointChecks as $inactiveSiteEndpointCheck)
            <x-compass::site-endpoint-check.row
                :site="$site"
                :path="$inactiveSiteEndpointCheck->path"
                :site-endpoint-check="$inactiveSiteEndpointCheck">
                <x-compass::badge type="neutral">{{ __('compass::site.inactive') }}</x-compass::badge>
            </x-compass::site-endpoint-check.row>
        @endforeach
    </x-slot:more-rows>
</x-compass::list>
