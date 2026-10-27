<header class="border-b border-border bg-canvas-inset">
    <div class="mx-auto max-w-7xl px-4 md:px-6 lg:px-8">
        <div class="flex items-center justify-between py-4">
            <a href="{{ $dashboardUrl }}" class="text-base font-semibold text-foreground"><x-compass::application-name /></a>

            <div class="flex items-center gap-2">
                @if ($lastCollectedAt !== null)
                    <span class="text-xs text-foreground-muted">
                        <x-compass::time :time="$lastCollectedAt" />
                    </span>
                @endif

                <form method="POST" action="{{ $syncUrl }}">
                    @csrf
                    <x-compass::button title="{{ __('compass::navigation.sync') }}" aria-label="{{ __('compass::navigation.sync') }}">
                        <x-compass::icons.sync class="text-foreground-muted" />
                    </x-compass::button>
                </form>

                <details x-data class="relative ml-2" @click.outside="$el.removeAttribute('open')">
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

                        <form method="POST" action="{{ $logoutUrl }}">
                            @csrf
                            <button type="submit" class="w-full cursor-pointer px-4 py-2 text-left text-sm text-foreground hover:bg-canvas-subtle">{{ __('compass::authentication.sign_out') }}</button>
                        </form>
                    </div>
                </details>
            </div>
        </div>

        <nav class="flex items-center gap-4 text-sm">
            <x-compass::tabs :tabs="$tabs" />
        </nav>
    </div>
</header>
