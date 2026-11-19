<x-compass::list :attributes="$attributes">
    <x-slot:icon>
        <x-compass::icons.alert />
    </x-slot:icon>
    <x-slot:title>{{ __('compass::alert.alerts') }}</x-slot:title>
    <x-slot:empty>{{ __('compass::alert.none') }}</x-slot:empty>
    <x-slot:counts>
        @foreach ($alertLevelCounts as $alertLevelCount)
            <x-compass::alert-level-count-badge :alert-level-count="$alertLevelCount" />
        @endforeach
        @if ($mutedOpenAlertCount > 0)
            <x-compass::badge type="neutral">{{ __('compass::alert.muted_count', ['count' => $mutedOpenAlertCount]) }}</x-compass::badge>
        @endif
        @if ($doneAlertCount > 0)
            <x-compass::badge type="neutral">{{ __('compass::alert.done_count', ['count' => $doneAlertCount]) }}</x-compass::badge>
        @endif
    </x-slot:counts>
    <x-slot:rows>
        @foreach ($unmutedOpenAlerts as $unmutedOpenAlert)
            <x-compass::alert-row :alert="$unmutedOpenAlert" />
        @endforeach
    </x-slot:rows>
    <x-slot:more-rows>
        @foreach ($mutedOrDoneAlerts as $mutedOrDoneAlert)
            <x-compass::alert-row :alert="$mutedOrDoneAlert" />
        @endforeach
    </x-slot:more-rows>
</x-compass::list>
