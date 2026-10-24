<x-compass::alerts.row :url="route(\Maarheeze\Compass\Http\RouteName::FORGE_SITE, $alert->site)" :name="$alert->site->name">
    <x-slot:icon>
        <x-compass::icons.server />
    </x-slot:icon>
    <x-slot:text>
        missing since
        <x-compass::time :time="$alert->site->missing_at" />
    </x-slot:text>
</x-compass::alerts.row>
