<x-compass::alerts.row :url="route(\Maarheeze\Compass\Http\RouteName::FORGE_SITE, $alert->site)" :name="$alert->site->name">
    <x-slot:icon>
        <x-compass::icons.alert />
    </x-slot:icon>
    <x-slot:text>
        Environment not set to production
    </x-slot:text>

    <x-compass::forge.link :site="$alert->site" />
</x-compass::alerts.row>
