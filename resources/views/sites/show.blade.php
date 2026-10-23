<x-compass::layouts.app :title="$site->name">
    <x-compass::navigation :section="\Maarheeze\Compass\View\NavigationSection::SITES" />

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
                        <a href="{{ route(\Maarheeze\Compass\Http\RouteName::GITHUB_REPOSITORY, $site->repository) }}" class="text-accent hover:underline">{{ $site->repository->name }}</a>
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
                <span>&middot;</span>
                <span>{{ $site->status->value }}</span>
                @if ($site->php_version)
                    <span>&middot;</span>
                    <span>PHP {{ $site->php_version }}</span>
                @endif
                <span>&middot;</span>
                <span title="{{ $site->server_ip_address }}">{{ $site->server_name }}</span>
                <span>&middot;</span>
                <span>
                    collected
                    <x-compass::time :time="$site->collected_at" />
                </span>
            </p>
        </div>

        <section class="space-y-3">
            <x-compass::section-heading>
                <x-slot:icon>
                    <x-compass::icons.rocket />
                </x-slot:icon>
                Latest deployment
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
                                finished
                                <x-compass::time :time="$site->deployment_ended_at" />
                            </span>
                        @elseif ($site->deployment_started_at)
                            <span>&middot;</span>
                            <span>
                                started
                                <x-compass::time :time="$site->deployment_started_at" />
                            </span>
                        @endif
                    </p>
                @else
                    <p class="text-foreground-muted">Never deployed.</p>
                @endif
            </div>
        </section>
    </main>
</x-compass::layouts.app>
