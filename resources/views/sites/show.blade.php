<x-compass::layouts.app>
    <x-slot:title>{{ $site->name }}</x-slot:title>
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <x-compass::site-missing-notice :site="$site" />

        <div class="space-y-1">
            <div class="flex items-center gap-3">
                <x-compass::subject-name :subject="$site">
                    <h1 class="text-lg font-semibold text-foreground">{{ $site->name }}</h1>
                </x-compass::subject-name>
                <x-compass::deployment-state-badge :site="$site" />
            </div>
            <p class="flex flex-wrap items-center gap-x-2 text-sm text-foreground-muted">
                <a href="{{ $site->url }}" target="_blank" rel="noopener" class="text-accent hover:underline">{{ $site->url }}</a>
                @if ($site->repository)
                    <span>&middot;</span>
                    <span>
                        <x-compass::repository-link :repository="$site->repository" />
                        @if ($site->repository_branch)
                            &#64; {{ $site->repository_branch }}
                        @endif
                    </span>
                @elseif ($site->repository_url)
                    <span>&middot;</span>
                    <span>
                        {{ $site->repository_url }}
                        @if ($site->repository_branch)
                            &#64; {{ $site->repository_branch }}
                        @endif
                    </span>
                @endif
                @if ($site->auto_deploy !== null)
                    <span>&middot;</span>
                    <span>
                        @if ($site->auto_deploy)
                            {{ __('compass::site.auto_deploy_on') }}
                        @else
                            {{ __('compass::site.auto_deploy_off') }}
                        @endif
                    </span>
                @endif
                @if ($site->php_version)
                    <span>&middot;</span>
                    <span>{{ $site->php_version }}</span>
                @endif
                <span>&middot;</span>
                <span title="{{ $site->server_ip_address }}">{{ $site->server_name }}</span>
                <span>&middot;</span>
                <span>
                    {{ __('compass::site.collected') }}
                    <x-compass::time :time="$site->collected_at" />
                </span>
            </p>
        </div>

        <section class="space-y-3">
            <x-compass::section-heading>
                <x-slot:icon>
                    <x-compass::icons.alert />
                </x-slot:icon>
                {{ __('compass::alert.alerts') }}
            </x-compass::section-heading>
            <x-compass::alert-table :alerts="$alerts" data-compass-live-region="alerts" />
        </section>

        <section class="space-y-3">
            <x-compass::section-heading>
                <x-slot:icon>
                    <x-compass::icons.rocket />
                </x-slot:icon>
                {{ __('compass::site.latest_deployment') }}
            </x-compass::section-heading>

            <div class="rounded-md border border-border bg-canvas px-4 py-3 text-sm">
                <p class="flex flex-wrap items-center gap-x-2 text-foreground-muted">
                    <span>
                        @if ($site->deployment_status !== null)
                            <x-compass::enum-label :labelled-enum="$site->deployment_status" />
                        @endif
                    </span>
                    @if ($site->deployment_ended_at)
                        <span>&middot;</span>
                        <span>
                            {{ __('compass::site.finished') }}
                            <x-compass::time :time="$site->deployment_ended_at" />
                        </span>
                    @elseif ($site->deployment_started_at)
                        <span>&middot;</span>
                        <span>
                            {{ __('compass::site.started') }}
                            <x-compass::time :time="$site->deployment_started_at" />
                        </span>
                    @endif
                </p>
            </div>
        </section>

        <section class="space-y-3">
            <x-compass::section-heading>
                <x-slot:icon>
                    <x-compass::icons.file-code />
                </x-slot:icon>
                {{ __('compass::site.reports') }}
            </x-compass::section-heading>

            <div class="space-y-4 rounded-md border border-border bg-canvas px-4 py-3 text-sm">
                @forelse ($site->reports as $siteReport)
                    <div class="flex items-start gap-4">
                        <div class="flex-1">
                            <x-compass::site-report-details :site-report="$siteReport" />
                        </div>

                        <x-compass::site-report-edit-link :site-report="$siteReport" />
                    </div>
                @empty
                    <x-compass::site-report-not-checked-badge />
                @endforelse
            </div>
        </section>

        <section class="space-y-3">
            <x-compass::section-heading>
                <x-slot:icon>
                    <x-compass::icons.check-circle />
                </x-slot:icon>
                {{ __('compass::site.endpoint_history') }}
            </x-compass::section-heading>

            <div class="overflow-hidden rounded-md border border-border bg-canvas">
                <table class="w-full table-fixed text-left text-sm">
                    <tbody class="divide-y divide-border">
                        @foreach ($activeSiteEndpointChecks as $activeSiteEndpointCheck)
                            <x-compass::site-endpoint-check.row :site="$site" :site-endpoint-check="$activeSiteEndpointCheck" />
                        @endforeach
                        @foreach ($inactiveSiteEndpointChecks as $inactiveSiteEndpointCheck)
                            <x-compass::site-endpoint-check.row :site="$site" :site-endpoint-check="$inactiveSiteEndpointCheck">
                                <x-compass::badge type="neutral">{{ __('compass::site.inactive') }}</x-compass::badge>
                            </x-compass::site-endpoint-check.row>
                        @endforeach
                        @if ($activeSiteEndpointChecks->isEmpty() && $inactiveSiteEndpointChecks->isEmpty())
                            <tr>
                                <td class="px-4 py-6 text-center text-foreground-muted">{{ __('compass::site.no_endpoints_checked') }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</x-compass::layouts.app>
