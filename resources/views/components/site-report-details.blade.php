<div class="space-y-2">
    <dl class="space-y-2">
        <x-compass::fact :label="__('compass::site-report.source')" class="flex items-center gap-1">
            <span class="font-mono">{{ $siteReport->source }}</span>
            <x-compass::site-report-edit-link :site-report="$siteReport" class="ml-auto" />
        </x-compass::fact>

        <x-compass::fact :label="__('compass::site-report.status')">
            <x-compass::site-report-status-badge :status="$siteReport->status" />
            <span class="text-foreground-muted">&middot; <x-compass::time :time="$siteReport->collected_at" /></span>
        </x-compass::fact>
    </dl>

    <details>
        <summary class="w-fit cursor-pointer list-none text-xs text-accent hover:underline [&::-webkit-details-marker]:hidden">{{ __('compass::site-report.details') }}</summary>

        <dl class="mt-2 space-y-2">
            <x-compass::fact :label="__('compass::site-report.application')">
                @if ($siteReport->app_name === null)
                    {{ __('compass::site.unknown') }}
                @else
                    {{ $siteReport->app_name }}
                @endif
            </x-compass::fact>

            <x-compass::fact :label="__('compass::site-report.environment')">
                @if ($siteReport->app_env === null)
                    {{ __('compass::site.unknown') }}
                @else
                    {{ $siteReport->app_env }}
                @endif
            </x-compass::fact>

            <x-compass::fact :label="__('compass::site-report.debug')">
                @if ($siteReport->app_debug === null)
                    {{ __('compass::site.unknown') }}
                @elseif ($siteReport->app_debug)
                    {{ __('compass::site-report.on') }}
                @else
                    {{ __('compass::site-report.off') }}
                @endif
            </x-compass::fact>

            <x-compass::fact :label="__('compass::site-report.maintenance_mode')">
                @if ($siteReport->maintenance_mode === null)
                    {{ __('compass::site.unknown') }}
                @elseif ($siteReport->maintenance_mode)
                    {{ __('compass::site-report.on') }}
                @else
                    {{ __('compass::site-report.off') }}
                @endif
            </x-compass::fact>
        </dl>
    </details>
</div>
