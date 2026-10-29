@if ($hasAlertsNotMuted)
    <span
        title="{{ __('compass::alert.has_alerts') }}"
        aria-label="{{ __('compass::alert.has_alerts') }}"
        class="flex text-amber-700 dark:text-amber-400">
        <x-compass::icons.alert />
    </span>
@else
    <span
        title="{{ __('compass::alert.all_alerts_muted') }}"
        aria-label="{{ __('compass::alert.all_alerts_muted') }}"
        class="flex text-foreground-muted">
        <x-compass::icons.alert />
    </span>
@endif
