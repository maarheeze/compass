<x-compass::layouts.app>
    <x-slot:title>{{ $site->name }}</x-slot:title>
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <div class="space-y-1">
            <h1 class="text-lg font-semibold text-foreground">{{ $site->name }}</h1>
            <p class="text-sm text-foreground-muted">{{ __('compass::site.settings_explanation') }}</p>
        </div>

        <x-compass::site-form :site="$site" :key="$key" />
    </main>
</x-compass::layouts.app>
