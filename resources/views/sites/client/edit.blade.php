<x-compass::layouts.app :title="$site->name">
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <div class="space-y-1">
            <h1 class="text-lg font-semibold text-foreground">{{ $site->name }}</h1>
            <p class="text-sm text-foreground-muted">{{ __('compass::client.settings_explanation') }}</p>
        </div>

        <x-compass::site-client-form :site="$site" :client-key="$clientKey" :client-path="$clientPath" />
    </main>
</x-compass::layouts.app>
