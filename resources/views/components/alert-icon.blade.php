@if ($alertLevelCounts->isNotEmpty())
    <span class="flex items-center gap-2">
        @foreach ($alertLevelCounts as $alertLevelCount)
            <x-compass::alert-level-count-badge :alert-level-count="$alertLevelCount" />
        @endforeach
    </span>
@else
    <span
        title="{{ __('compass::alert.all_alerts_muted') }}"
        aria-label="{{ __('compass::alert.all_alerts_muted') }}"
        class="flex text-foreground-muted">
        <x-compass::icons.alert />
    </span>
@endif
