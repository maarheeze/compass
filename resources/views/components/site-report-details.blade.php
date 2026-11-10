<dl class="grid grid-cols-[max-content_1fr] gap-x-6 gap-y-2">
    <dt class="text-foreground-muted">{{ __('compass::site-report.source') }}</dt>
    <dd class="font-mono text-foreground">{{ $siteReport->source }}</dd>

    <dt class="text-foreground-muted">{{ __('compass::site-report.status') }}</dt>
    <dd class="flex items-center gap-2 text-foreground">
        <x-compass::site-report-status-badge :status="$siteReport->status" />
        <span class="text-foreground-muted">
            {{ __('compass::site-report.checked') }}
            <x-compass::time :time="$siteReport->collected_at" />
        </span>
    </dd>

    @if ($siteReport->app_name !== null)
        <dt class="text-foreground-muted">{{ __('compass::site-report.application') }}</dt>
        <dd class="text-foreground">{{ $siteReport->app_name }}</dd>
    @endif

    @if ($siteReport->app_env !== null)
        <dt class="text-foreground-muted">{{ __('compass::site-report.environment') }}</dt>
        <dd class="text-foreground">{{ $siteReport->app_env }}</dd>
    @endif

    @if ($siteReport->app_debug !== null)
        <dt class="text-foreground-muted">{{ __('compass::site-report.debug') }}</dt>
        <dd class="text-foreground">
            @if ($siteReport->app_debug)
                {{ __('compass::site-report.on') }}
            @else
                {{ __('compass::site-report.off') }}
            @endif
        </dd>
    @endif

    @if ($siteReport->endpoints->isNotEmpty())
        <dt class="text-foreground-muted">{{ __('compass::site-report.endpoints') }}</dt>
        <dd>
            <ul class="flex flex-wrap gap-2">
                @foreach ($siteReport->endpoints as $endpoint)
                    <li class="rounded-md bg-border/50 px-2 py-0.5 font-mono text-xs text-foreground-muted">{{ $endpoint->path->toString() }}</li>
                @endforeach
            </ul>
        </dd>
    @endif

    <dt class="text-foreground-muted">{{ __('compass::site-report.path') }}</dt>
    <dd class="font-mono text-foreground">
        @if ($siteReport->path === null)
            <span class="font-sans text-foreground-muted">{{ __('compass::site-report.default') }}</span>
        @else
            {{ $siteReport->path }}
        @endif
    </dd>

    <dt class="text-foreground-muted">{{ __('compass::site-report.key') }}</dt>
    <dd class="break-all font-mono text-foreground">
        @if ($siteReport->key === null)
            <span class="font-sans text-foreground-muted">{{ __('compass::site-report.default') }}</span>
        @else
            {{ $siteReport->key }}
        @endif
    </dd>
</dl>
