<x-compass::alerts.row>
    <x-slot:link>
        <x-compass::site-link :site="$alert->siteEndpointCheck->site" class="font-medium" />
    </x-slot:link>
    <x-slot:icon>
        <x-compass::icons.alert />
    </x-slot:icon>
    <x-slot:text>
        <span class="font-mono">{{ $alert->siteEndpointCheck->path->path }}</span>
        @if ($alert->siteEndpointCheck->status_code === null)
            {{ __('compass::alert.endpoint_unreachable') }}
        @else
            {{ __('compass::alert.endpoint_responded', ['status_code' => $alert->siteEndpointCheck->status_code]) }}
        @endif
        <x-compass::time :time="$alert->siteEndpointCheck->created_at" />
    </x-slot:text>

    <x-compass::forge.link :site="$alert->siteEndpointCheck->site" />
</x-compass::alerts.row>
