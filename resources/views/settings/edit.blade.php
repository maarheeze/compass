<x-compass::layouts.app :title="__('compass::settings.settings')">
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <div class="space-y-1">
            <h1 class="text-lg font-semibold text-foreground">{{ __('compass::settings.notifications') }}</h1>
            <p class="text-sm text-foreground-muted">{{ __('compass::settings.notifications_explanation') }}</p>
        </div>

        <form method="POST" action="{{ $updateUrl }}" class="space-y-6">
            @csrf
            @method('PATCH')

            @error('notification_channels*')
                <p class="rounded-md border border-red-600/30 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-400/30 dark:bg-red-400/10 dark:text-red-300">{{ __('compass::settings.notifications_refused') }}</p>
            @enderror

            @foreach ($groupedAlertTypes as $alertTypesOfGroup)
                <section class="overflow-hidden rounded-md border border-border bg-canvas text-sm">
                    <h2 class="border-b border-border bg-canvas-subtle px-4 py-2 font-semibold text-foreground">
                        <x-compass::enum-label :value="$alertTypesOfGroup->alertTypeGroup" />
                    </h2>

                    <ul class="divide-y divide-border">
                        @foreach ($alertTypesOfGroup->alertTypes as $alertType)
                            <li class="flex items-center justify-between px-4 py-2">
                                <span class="text-foreground">
                                    <x-compass::alert-type-label :identifier="$alertType->identifier()" />
                                </span>
                                <div class="flex items-center gap-4">
                                    @foreach ($notificationChannels as $notificationChannel)
                                        <x-compass::notification-channel-checkbox
                                            :alert-type="$alertType"
                                            :notification-channel="$notificationChannel"
                                            :notification-subscriptions="$notificationSubscriptions" />
                                    @endforeach
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach

            <x-compass::primary-button>{{ __('compass::settings.save') }}</x-compass::primary-button>
        </form>
    </main>
</x-compass::layouts.app>
