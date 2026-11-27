<x-compass::layouts.app>
    <x-slot:title>{{ $site->name }}</x-slot:title>
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <x-compass::site-missing-notice :site="$site" />

        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <x-compass::site-title :site="$site">
                    <h1 class="text-lg font-semibold text-foreground">{{ $site->name }}</h1>
                </x-compass::site-title>
                <div class="ml-auto flex items-center gap-2">
                    <x-compass::source-page-link :source-url="$site->source_url" />
                    <x-compass::site-inspect-button :site="$site" />
                </div>
            </div>
            <a href="{{ $site->url }}" target="_blank" rel="noopener" class="text-sm text-accent hover:underline">{{ $site->url }}</a>
        </div>

        <x-compass::page-columns>
            @foreach ($site->reports as $siteReport)
                <x-compass::site-client-install-notice :site-report="$siteReport" />
                <x-compass::site-client-key-notice :site-report="$siteReport" />
            @endforeach

            <x-compass::alert-table :alerts="$alerts" data-compass-live-region="alerts" />

            <x-compass::alert-check-table :alert-checks="$alertChecks" />

            <x-compass::site-endpoint-table
                :site="$site"
                :reported-site-endpoints="$reportedSiteEndpoints"
                :inactive-site-endpoint-checks="$inactiveSiteEndpointChecks" />

            <x-slot:aside>
                <x-compass::sidebar-section :title="__('compass::site.about')">
                    <x-slot:action>
                        <x-compass::site-edit-link :site="$site" />
                    </x-slot:action>

                    <dl class="space-y-2">
                        <x-compass::fact :label="__('compass::site.repository')">
                            @if ($site->repository)
                                <x-compass::repository-link :repository="$site->repository" />
                            @elseif ($site->repository_url)
                                {{ $site->repository_url }}
                            @else
                                &mdash;
                            @endif
                            @if ($site->repository_branch)
                                <span class="text-foreground-muted">&#64; {{ $site->repository_branch }}</span>
                            @endif
                        </x-compass::fact>
                        <x-compass::fact :label="__('compass::site.server')">
                            <span title="{{ $site->server_ip_address }}">{{ $site->server_name }}</span>
                            @if ($site->php_version)
                                <span class="text-foreground-muted">&middot; {{ $site->php_version }}</span>
                            @endif
                        </x-compass::fact>
                        <x-compass::fact :label="__('compass::site.domains')">
                            @forelse ($site->domains as $siteDomain)
                                <div>
                                    {{ $siteDomain->name }}
                                    <span class="text-foreground-muted">
                                        &middot; <x-compass::enum-label :labelled-enum="$siteDomain->type" />
                                        &middot; <x-compass::enum-label :labelled-enum="$siteDomain->status" />
                                    </span>
                                </div>
                            @empty
                                {{ __('compass::site.unknown') }}
                            @endforelse
                        </x-compass::fact>
                        <x-compass::fact :label="__('compass::site.queue_worker')">
                            @if ($site->queue_worker_state === null)
                                {{ __('compass::site.unknown') }}
                            @else
                                <x-compass::enum-label :labelled-enum="$site->queue_worker_state" />
                                @if ($site->processes_collected_at !== null)
                                    <span class="text-foreground-muted">&middot; <x-compass::time :time="$site->processes_collected_at" /></span>
                                @endif
                            @endif
                        </x-compass::fact>
                    </dl>
                </x-compass::sidebar-section>

                <x-compass::sidebar-section :title="__('compass::site.deployment')">
                    <dl class="space-y-2">
                        @if ($site->deployment_status !== null)
                            <x-compass::fact :label="__('compass::site.latest_deployment')">
                                <x-compass::deployment-status-badge :status="$site->deployment_status" />
                                @if ($site->latestDeploymentTime() !== null)
                                    <span class="text-foreground-muted">&middot; <x-compass::time :time="$site->latestDeploymentTime()" /></span>
                                @endif
                            </x-compass::fact>
                        @endif
                        <x-compass::fact :label="__('compass::site.auto_deploy')">
                            @if ($site->auto_deploy === null)
                                {{ __('compass::site.unknown') }}
                            @elseif ($site->auto_deploy)
                                {{ __('compass::site.auto_deploy_on') }}
                            @else
                                {{ __('compass::site.auto_deploy_off') }}
                            @endif
                        </x-compass::fact>
                    </dl>
                </x-compass::sidebar-section>

                <x-compass::sidebar-section :title="__('compass::site.report')">
                    <div data-compass-live-region="site-reports" class="space-y-3">
                        @forelse ($site->reports as $siteReport)
                            <x-compass::site-report-details :site-report="$siteReport" />
                        @empty
                            <x-compass::site-report-not-checked-badge />
                        @endforelse
                    </div>
                </x-compass::sidebar-section>

                <p class="text-xs text-foreground-muted">
                    {{ __('compass::site.collected') }}
                    <x-compass::time :time="$site->collected_at" />
                </p>
            </x-slot:aside>
        </x-compass::page-columns>
    </main>
</x-compass::layouts.app>
