<x-compass::alerts.row>
    <x-slot:link>
        <x-compass::site-link :site="$alert->site" class="font-medium" />
    </x-slot:link>
    <x-slot:icon>
        <x-compass::icons.server />
    </x-slot:icon>
    <x-slot:text>
        {{ __('compass::alert.missing_since') }}
        <x-compass::time :time="$alert->site->missing_at" />
    </x-slot:text>
</x-compass::alerts.row>
