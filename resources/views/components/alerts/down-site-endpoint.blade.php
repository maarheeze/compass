<x-compass::alerts.row :url="route(\Maarheeze\Compass\Http\RouteName::FORGE_SITE, $alert->siteEndpointCheck->site)" :name="$alert->siteEndpointCheck->site->name">
    <x-slot:icon>
        <x-compass::icons.alert />
    </x-slot:icon>
    <x-slot:text>
        <span class="font-mono">{{ $alert->siteEndpointCheck->path }}</span>
        @if ($alert->siteEndpointCheck->status_code === null)
            unreachable
        @else
            responded {{ $alert->siteEndpointCheck->status_code }}
        @endif
        <x-compass::time :time="$alert->siteEndpointCheck->created_at" />
    </x-slot:text>

    <x-compass::forge.link :site="$alert->siteEndpointCheck->site" />
</x-compass::alerts.row>
