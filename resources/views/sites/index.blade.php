<x-compass::layouts.app :title="__('compass::navigation.sites')">
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <div class="overflow-hidden rounded-md border border-border bg-canvas">
            <table class="w-full table-fixed text-left text-sm">
                <tbody class="divide-y divide-border">
                    @forelse ($sites as $site)
                        <x-compass::site-row :site="$site" />
                    @empty
                        <tr>
                            <td class="px-4 py-6 text-center text-foreground-muted">{{ __('compass::site.no_sites') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</x-compass::layouts.app>
