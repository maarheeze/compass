<x-compass::layouts.app>
    <x-slot:title>{{ __('compass::navigation.dashboard') }}</x-slot:title>
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <x-compass::no-sources-notice />

        <x-compass::alert-table :alerts="$alerts" data-compass-live-region="alerts" />

        <x-compass::package-versions />
    </main>
</x-compass::layouts.app>
