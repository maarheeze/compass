<dl class="grid grid-cols-[max-content_1fr] gap-x-6 gap-y-2">
    <dt class="text-foreground-muted">{{ __('compass::client.source') }}</dt>
    <dd class="font-mono text-foreground">{{ $siteReport->source }}</dd>

    <dt class="text-foreground-muted">{{ __('compass::client.status') }}</dt>
    <dd class="flex items-center gap-2 text-foreground">
        <x-compass::client-status-badge :status="$siteReport->status" />
        @if ($siteReport->collected_at)
            <span class="text-foreground-muted">
                {{ __('compass::client.checked') }}
                <x-compass::time :time="$siteReport->collected_at" />
            </span>
        @endif
    </dd>

    @if ($siteReport->app_name !== null)
        <dt class="text-foreground-muted">{{ __('compass::client.application') }}</dt>
        <dd class="text-foreground">{{ $siteReport->app_name }}</dd>
    @endif

    @if ($siteReport->app_env !== null)
        <dt class="text-foreground-muted">{{ __('compass::client.environment') }}</dt>
        <dd class="text-foreground">{{ $siteReport->app_env }}</dd>
    @endif

    @if ($siteReport->app_debug !== null)
        <dt class="text-foreground-muted">{{ __('compass::client.debug') }}</dt>
        <dd class="text-foreground">
            @if ($siteReport->app_debug)
                {{ __('compass::client.on') }}
            @else
                {{ __('compass::client.off') }}
            @endif
        </dd>
    @endif

    @if ($siteReport->endpoints->isNotEmpty())
        <dt class="text-foreground-muted">{{ __('compass::client.endpoints') }}</dt>
        <dd>
            <ul class="flex flex-wrap gap-2">
                @foreach ($siteReport->endpoints as $endpoint)
                    <li class="rounded-md bg-border/50 px-2 py-0.5 font-mono text-xs text-foreground-muted">{{ $endpoint->path }}</li>
                @endforeach
            </ul>
        </dd>
    @endif

    <dt class="text-foreground-muted">{{ __('compass::client.path') }}</dt>
    <dd class="font-mono text-foreground">
        @if ($siteReport->path === null)
            <span class="font-sans text-foreground-muted">{{ __('compass::client.default') }}</span>
        @else
            {{ $siteReport->path }}
        @endif
    </dd>

    <dt class="text-foreground-muted">{{ __('compass::client.key') }}</dt>
    <dd class="break-all font-mono text-foreground">
        @if ($siteReport->key === null)
            <span class="font-sans text-foreground-muted">{{ __('compass::client.default') }}</span>
        @else
            {{ $siteReport->key }}
        @endif
    </dd>
</dl>
