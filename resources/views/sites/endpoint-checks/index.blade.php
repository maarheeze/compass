<x-compass::layouts.app>
    <x-slot:title>{{ $site->name }}</x-slot:title>
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-lg font-semibold text-foreground">
                <x-compass::site-link :site="$site" />
                <span class="font-mono">{{ $endpointPath->toString() }}</span>
            </h1>

            <x-compass::site-endpoint-checks-delete-button :site="$site" :path="$endpointPath" class="ml-auto" />
        </div>

        <div class="overflow-hidden rounded-md border border-border bg-canvas">
            <table class="w-full table-fixed text-left text-sm">
                <tbody class="divide-y divide-border">
                    @forelse ($siteEndpointChecks as $siteEndpointCheck)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="flex min-w-0 items-center gap-2">
                                    <span class="text-base font-semibold text-foreground">
                                        @if ($siteEndpointCheck->status_code === null)
                                            {{ __('compass::endpoint-check.unreachable') }}
                                        @else
                                            {{ $siteEndpointCheck->status_code }}
                                        @endif
                                    </span>
                                    <x-compass::site-endpoint-check.status-label :site-endpoint-check="$siteEndpointCheck" />
                                </div>
                                <div class="mt-1 flex flex-wrap items-center gap-x-2 text-xs text-foreground-muted">
                                    <span class="truncate font-mono">{{ $siteEndpointCheck->url }}</span>
                                    <span>&middot;</span>
                                    <x-compass::date-time :time="$siteEndpointCheck->created_at" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-6 text-center text-foreground-muted">{{ __('compass::endpoint-check.no_checks') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $siteEndpointChecks->links('compass::pagination') }}
    </main>
</x-compass::layouts.app>
