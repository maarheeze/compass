@props(['status'])

@switch ($status)
    @case (\Maarheeze\Compass\Collector\Client\ClientStatus::INCOMPATIBLE)
        <span class="rounded-md bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700 ring-1 ring-red-600/20 dark:bg-red-400/10 dark:text-red-400 dark:ring-red-400/20">{{ __('compass::client-status.INCOMPATIBLE') }}</span>
        @break
    @case (\Maarheeze\Compass\Collector\Client\ClientStatus::INSTALLED)
        <span class="rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-400 dark:ring-emerald-400/20">{{ __('compass::client-status.INSTALLED') }}</span>
        @break
    @case (\Maarheeze\Compass\Collector\Client\ClientStatus::NOT_INSTALLED)
        <span class="rounded-md bg-border/50 px-2 py-0.5 text-xs font-medium text-foreground-muted ring-1 ring-border">{{ __('compass::client-status.NOT_INSTALLED') }}</span>
        @break
    @case (\Maarheeze\Compass\Collector\Client\ClientStatus::UNAUTHORIZED)
        <span class="rounded-md bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700 ring-1 ring-red-600/20 dark:bg-red-400/10 dark:text-red-400 dark:ring-red-400/20">{{ __('compass::client-status.UNAUTHORIZED') }}</span>
        @break
    @case (\Maarheeze\Compass\Collector\Client\ClientStatus::UNREACHABLE)
        <span class="rounded-md bg-border/50 px-2 py-0.5 text-xs font-medium text-foreground-muted ring-1 ring-border">{{ __('compass::client-status.UNREACHABLE') }}</span>
        @break
    @default
        <span class="rounded-md bg-border/50 px-2 py-0.5 text-xs font-medium text-foreground-muted ring-1 ring-border">{{ __('compass::client.not_checked') }}</span>
@endswitch
