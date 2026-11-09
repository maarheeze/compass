<header class="border-b border-border bg-canvas-inset">
    <div class="mx-auto max-w-7xl px-4 md:px-6 lg:px-8">
        <div class="flex items-center justify-between py-4">
            <a href="{{ $dashboardUrl }}" class="text-base font-semibold text-foreground"><x-compass::application-name /></a>

            <div class="flex items-center gap-2">
                @if ($hasAlertsNotMuted)
                    <a
                        href="{{ $dashboardUrl }}"
                        title="{{ __('compass::navigation.alerts_found') }}"
                        aria-label="{{ __('compass::navigation.alerts_found') }}"
                        class="rounded-md border border-amber-600/30 bg-amber-50 px-3 py-1.5 text-sm text-amber-700 hover:bg-amber-100 dark:border-amber-400/30 dark:bg-amber-400/10 dark:text-amber-400 dark:hover:bg-amber-400/20">
                        <x-compass::icons.alert />
                    </a>
                @elseif ($hasAlerts)
                    <a
                        href="{{ $dashboardUrl }}"
                        title="{{ __('compass::navigation.all_alerts_muted') }}"
                        aria-label="{{ __('compass::navigation.all_alerts_muted') }}"
                        class="rounded-md border border-border bg-canvas-subtle px-3 py-1.5 text-sm text-foreground-muted hover:bg-border/30">
                        <x-compass::icons.alert />
                    </a>
                @endif

                <details x-data class="relative ml-2" @click.outside="$el.removeAttribute('open')">
                    <summary class="flex cursor-pointer list-none items-center">
                        <x-compass::avatar :person="$user" :url="null" class="size-8" />
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

                        <a href="{{ $settingsUrl }}" class="block px-4 py-2 text-sm text-foreground hover:bg-canvas-subtle">{{ __('compass::settings.settings') }}</a>

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
