<x-compass::alerts.row>
    <x-slot:link>
        <x-compass::site-link :site="$alert->site" class="font-medium" />
    </x-slot:link>
    <x-slot:icon>
        <x-compass::icons.alert />
    </x-slot:icon>
    <x-slot:text>
        Client outdated
    </x-slot:text>

    <x-compass::forge.link :site="$alert->site" />
</x-compass::alerts.row>
