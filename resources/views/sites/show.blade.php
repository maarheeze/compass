<x-compass::layouts.app :title="$site->name">
    <x-compass::navigation :section="\Maarheeze\Compass\View\NavigationSection::SITES" />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <div class="flex items-center gap-3">
            <h1 class="text-lg font-semibold text-foreground">{{ $site->name }}</h1>
            <x-compass::forge.deployment-state-label :site="$site" />
            <x-compass::forge.link :site="$site" class="ml-auto" />
        </div>

        <section class="rounded-md border border-border bg-canvas p-4">
            <dl class="grid grid-cols-[max-content_1fr] gap-x-6 gap-y-2 text-sm">
                <dt class="text-foreground-muted">URL</dt>
                <dd>
                    <a href="{{ $site->url }}" target="_blank" rel="noopener" class="text-accent hover:underline">{{ $site->url }}</a>
                </dd>
                <dt class="text-foreground-muted">Status</dt>
                <dd>{{ $site->status->value }}</dd>
                <dt class="text-foreground-muted">PHP</dt>
                <dd>{{ $site->php_version }}</dd>
                <dt class="text-foreground-muted">Repository</dt>
                <dd>
                    @if ($site->repository)
                        <a href="{{ route(\Maarheeze\Compass\Http\RouteName::GITHUB_REPOSITORY, $site->repository) }}" class="text-accent hover:underline">{{ $site->repository->name }}</a>
                    @else
                        {{ $site->repository_url }}
                    @endif
                </dd>
                <dt class="text-foreground-muted">Branch</dt>
                <dd>{{ $site->repository_branch }}</dd>
                <dt class="text-foreground-muted">Server</dt>
                <dd>{{ $site->server_name }}</dd>
                <dt class="text-foreground-muted">Server IP address</dt>
                <dd>{{ $site->server_ip_address }}</dd>
                <dt class="text-foreground-muted">Collected at</dt>
                <dd>
                    <x-compass::time :time="$site->collected_at" />
                </dd>
            </dl>
        </section>

        <section class="space-y-3">
            <x-compass::section-heading>
                <x-slot:icon>
                    <x-compass::icons.rocket />
                </x-slot:icon>
                Latest deployment
            </x-compass::section-heading>

            @if ($site->deployment_status)
                <dl class="grid grid-cols-[max-content_1fr] gap-x-6 gap-y-2 text-sm">
                    <dt class="text-foreground-muted">Status</dt>
                    <dd>{{ $site->deployment_status->value }}</dd>
                    <dt class="text-foreground-muted">Commit</dt>
                    <dd><code>{{ $site->deployed_commit_hash }}</code></dd>
                    <dt class="text-foreground-muted">Message</dt>
                    <dd>{{ $site->deployed_commit_message }}</dd>
                    <dt class="text-foreground-muted">Started at</dt>
                    <dd>
                        @if ($site->deployment_started_at)
                            <x-compass::time :time="$site->deployment_started_at" />
                        @endif
                    </dd>
                    <dt class="text-foreground-muted">Ended at</dt>
                    <dd>
                        @if ($site->deployment_ended_at)
                            <x-compass::time :time="$site->deployment_ended_at" />
                        @endif
                    </dd>
                </dl>
            @else
                <p class="text-sm text-foreground-muted">Never deployed.</p>
            @endif
        </section>
    </main>
</x-compass::layouts.app>
