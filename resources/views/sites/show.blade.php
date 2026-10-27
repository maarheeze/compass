<x-compass::layouts.app :title="$site->name">
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        @if ($site->missing_at)
            <x-compass::missing-notice
                :missing-at="$site->missing_at"
                :missing-ignored-at="$site->missing_ignored_at"
                :delete-url="route(\Maarheeze\Compass\Http\RouteName::FORGE_SITE_DELETE, $site)"
                :ignore-url="route(\Maarheeze\Compass\Http\RouteName::FORGE_SITE_IGNORE, $site)" />
        @endif

        <div class="space-y-1">
            <div class="flex items-center gap-3">
                <h1 class="text-lg font-semibold text-foreground">{{ $site->name }}</h1>
                <x-compass::missing-label :missing-at="$site->missing_at" :missing-ignored-at="$site->missing_ignored_at" />
                <x-compass::forge.deployment-state-label :site="$site" />
                <x-compass::forge.link :site="$site" class="ml-auto" />
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
                <span>&middot;</span>
                <span>{{ $site->status->value }}</span>
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
                    <x-compass::icons.rocket />
                </x-slot:icon>
                {{ __('compass::site.latest_deployment') }}
            </x-compass::section-heading>

            <div class="rounded-md border border-border bg-canvas px-4 py-3 text-sm">
                @if ($site->deployment_status)
                    <p class="font-medium text-foreground">{{ $site->deployed_commit_message }}</p>
                    <p class="mt-1 flex flex-wrap items-center gap-x-2 text-foreground-muted">
                        <x-compass::commit-hash :repository="$site->repository" :commit-hash="$site->deployed_commit_hash" />
                        <span>&middot;</span>
                        <span>{{ $site->deployment_status->value }}</span>
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
                @else
                    <p class="text-foreground-muted">{{ __('compass::site.never_deployed') }}</p>
                @endif
            </div>
        </section>

        <section class="space-y-3">
            <x-compass::section-heading>
                <x-slot:icon>
                    <x-compass::icons.file-code />
                </x-slot:icon>
                {{ __('compass::site.client') }}
            </x-compass::section-heading>

            <div class="rounded-md border border-border bg-canvas px-4 py-3 text-sm">
                <div class="flex items-start gap-4">
                    <dl class="grid flex-1 grid-cols-[max-content_1fr] gap-x-6 gap-y-2">
                        <dt class="text-foreground-muted">{{ __('compass::client.status') }}</dt>
                        <dd class="flex items-center gap-2 text-foreground">
                            <x-compass::client.status-label :status="$site->client_status" />
                            @if ($site->client_collected_at)
                                <span class="text-foreground-muted">
                                    {{ __('compass::client.checked') }}
                                    <x-compass::time :time="$site->client_collected_at" />
                                </span>
                            @endif
                        </dd>

                        @if ($site->app_name)
                            <dt class="text-foreground-muted">{{ __('compass::client.application') }}</dt>
                            <dd class="text-foreground">{{ $site->app_name }}</dd>

                            <dt class="text-foreground-muted">{{ __('compass::client.environment') }}</dt>
                            <dd class="text-foreground">{{ $site->app_env }}</dd>

                            <dt class="text-foreground-muted">{{ __('compass::client.debug') }}</dt>
                            <dd class="text-foreground">
                                @if ($site->app_debug)
                                    {{ __('compass::client.on') }}
                                @else
                                    {{ __('compass::client.off') }}
                                @endif
                            </dd>
                        @endif

                        @if ($site->endpoints->isNotEmpty())
                            <dt class="text-foreground-muted">{{ __('compass::client.endpoints') }}</dt>
                            <dd>
                                <ul class="flex flex-wrap gap-2">
                                    @foreach ($site->endpoints as $endpoint)
                                        <li class="rounded-md bg-border/50 px-2 py-0.5 font-mono text-xs text-foreground-muted">{{ $endpoint->path }}</li>
                                    @endforeach
                                </ul>
                            </dd>
                        @endif

                        <dt class="text-foreground-muted">{{ __('compass::client.path') }}</dt>
                        <dd class="font-mono text-foreground">
                            {{ $clientPath }}
                            @if ($site->client_path === null)
                                <span class="font-sans text-foreground-muted">{{ __('compass::client.default') }}</span>
                            @endif
                        </dd>

                        <dt class="text-foreground-muted">{{ __('compass::client.key') }}</dt>
                        <dd class="break-all font-mono text-foreground">
                            @if ($clientKey)
                                {{ $clientKey }}
                                @if ($site->client_key === null)
                                    <span class="font-sans text-foreground-muted">{{ __('compass::client.default') }}</span>
                                @endif
                            @else
                                <span class="font-sans text-foreground-muted">{{ __('compass::client.not_set') }}</span>
                            @endif
                        </dd>
                    </dl>

                    <a href="{{ route(\Maarheeze\Compass\Http\RouteName::FORGE_SITE_CLIENT_EDIT, $site) }}" class="rounded-md border border-border bg-canvas-subtle px-3 py-1.5 text-sm font-medium text-foreground hover:bg-border/30">{{ __('compass::client.edit') }}</a>
                </div>
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
                <table class="w-full text-left text-sm">
                    <thead class="bg-canvas-subtle text-xs text-foreground-muted">
                        <tr>
                            <th class="px-4 py-2 font-medium">{{ __('compass::site.path') }}</th>
                            <th class="px-4 py-2 font-medium">{{ __('compass::site.status') }}</th>
                            <th class="px-4 py-2 font-medium">{{ __('compass::site.last_checked') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($latestSiteEndpointChecks as $latestSiteEndpointCheck)
                            <tr>
                                <td class="px-4 py-2">
                                    <a href="{{ route(\Maarheeze\Compass\Http\RouteName::FORGE_SITE_ENDPOINT_CHECKS, [$site, $latestSiteEndpointCheck->path->routeKey()]) }}" class="font-mono text-accent hover:underline">{{ $latestSiteEndpointCheck->path->path }}</a>
                                </td>
                                <td class="px-4 py-2">
                                    <x-compass::site-endpoint-check.status-label :site-endpoint-check="$latestSiteEndpointCheck" />
                                </td>
                                <td class="px-4 py-2 text-foreground-muted">
                                    <x-compass::time :time="$latestSiteEndpointCheck->created_at" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-foreground-muted">{{ __('compass::site.no_endpoints_checked') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</x-compass::layouts.app>
