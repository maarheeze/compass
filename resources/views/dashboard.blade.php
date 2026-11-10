<x-compass::layouts.app>
    <x-slot:title>{{ __('compass::navigation.dashboard') }}</x-slot:title>
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <section class="space-y-3">
            <x-compass::section-heading>
                <x-slot:icon>
                    <x-compass::icons.alert />
                </x-slot:icon>
                {{ __('compass::alert.alerts') }}
            </x-compass::section-heading>
            <x-compass::alert-table :alerts="$alerts" />
        </section>
    </main>
</x-compass::layouts.app>
