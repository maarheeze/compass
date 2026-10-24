<x-compass::alerts.row :url="route(\Maarheeze\Compass\Http\RouteName::FORGE_SITE, $alert->site)" :name="$alert->site->name">
    <x-slot:icon>
        <x-compass::icons.alert />
    </x-slot:icon>
    <x-slot:text>
        Client key refused
    </x-slot:text>

    <x-compass::forge.link :site="$alert->site" />
</x-compass::alerts.row>
