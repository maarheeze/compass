<header class="border-b border-border bg-canvas-inset">
    <div class="mx-auto max-w-7xl px-4 md:px-6 lg:px-8">
        <div class="flex items-center justify-between py-4">
            <a href="{{ route(\Maarheeze\Compass\Http\RouteName::DASHBOARD) }}" class="text-base font-semibold text-foreground">Compass</a>

            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route(\Maarheeze\Compass\Http\RouteName::GITHUB_SYNC) }}">
                    @csrf
                    <x-compass::button>Sync GitHub</x-compass::button>
                </form>

                <form method="POST" action="{{ route(\Maarheeze\Compass\Http\RouteName::FORGE_SYNC) }}">
                    @csrf
                    <x-compass::button>Sync Forge</x-compass::button>
                </form>

                <details class="relative ml-2">
                    <summary class="flex cursor-pointer list-none items-center">
                        @if ($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->nickname }}" class="size-8 rounded-full border border-border">
                        @else
                            <span class="text-sm font-medium text-foreground">{{ $user->nickname }}</span>
                        @endif
                    </summary>

                    <div class="absolute right-0 z-10 mt-2 w-56 rounded-md border border-border bg-canvas py-1 shadow-lg">
                        <div class="border-b border-border px-4 py-2">
                            <p class="text-sm font-semibold text-foreground">
                                @if ($user->name)
                                    {{ $user->name }}
                                @else
                                    {{ $user->nickname }}
                                @endif
                            </p>
                            <p class="text-xs text-foreground-muted">&#64;{{ $user->nickname }}</p>
                        </div>

                        <form method="POST" action="{{ route(\Maarheeze\Compass\Http\RouteName::LOGOUT) }}">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 text-left text-sm text-foreground hover:bg-canvas-subtle">Sign out</button>
                        </form>
                    </div>
                </details>
            </div>
        </div>

        <nav class="flex items-center gap-4 text-sm">
            <x-compass::tab :route-name="\Maarheeze\Compass\Http\RouteName::DASHBOARD" :is-active="$section === \Maarheeze\Compass\View\NavigationSection::DASHBOARD">
                <x-compass::icons.home class="text-foreground-muted" />
                Dashboard
            </x-compass::tab>
            <x-compass::tab :route-name="\Maarheeze\Compass\Http\RouteName::GITHUB_REPOSITORIES" :is-active="$section === \Maarheeze\Compass\View\NavigationSection::REPOSITORIES">
                <x-compass::icons.repo class="text-foreground-muted" />
                Repositories
            </x-compass::tab>
            <x-compass::tab :route-name="\Maarheeze\Compass\Http\RouteName::GITHUB_ISSUES" :is-active="$section === \Maarheeze\Compass\View\NavigationSection::ISSUES">
                <x-compass::icons.issue-opened class="text-foreground-muted" />
                Issues
            </x-compass::tab>
            <x-compass::tab :route-name="\Maarheeze\Compass\Http\RouteName::GITHUB_PULL_REQUESTS" :is-active="$section === \Maarheeze\Compass\View\NavigationSection::PULL_REQUESTS">
                <x-compass::icons.git-pull-request class="text-foreground-muted" />
                Pull requests
            </x-compass::tab>
            <x-compass::tab :route-name="\Maarheeze\Compass\Http\RouteName::FORGE_SITES" :is-active="$section === \Maarheeze\Compass\View\NavigationSection::SITES">
                <x-compass::icons.server class="text-foreground-muted" />
                Sites
            </x-compass::tab>
        </nav>
    </div>
</header>
