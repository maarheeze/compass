<x-compass::notice type="warning">
    <x-compass::icons.alert />
    <div class="space-y-1">
        <p>{{ __('compass::site-report.client_key_notice') }}</p>
        <code class="block break-all font-mono">COMPASS_CLIENT_KEY={{ $key }}</code>
    </div>
</x-compass::notice>
