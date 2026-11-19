<x-compass::list :attributes="$attributes">
    <x-slot:icon>
        <x-compass::icons.check-circle />
    </x-slot:icon>
    <x-slot:title>{{ __('compass::alert-check.checks') }}</x-slot:title>
    <x-slot:empty>{{ __('compass::alert-check.none') }}</x-slot:empty>
    <x-slot:counts>
        @foreach ($alertCheckStateCounts as $alertCheckStateCount)
            <x-compass::alert-check-state-count-badge :alert-check-state-count="$alertCheckStateCount" />
        @endforeach
    </x-slot:counts>
    <x-slot:rows>
        @foreach ($failingAlertChecks as $failingAlertCheck)
            <x-compass::alert-check-row :alert-check="$failingAlertCheck" />
        @endforeach
    </x-slot:rows>
    <x-slot:more-rows>
        @foreach ($notFailingAlertChecks as $notFailingAlertCheck)
            <x-compass::alert-check-row :alert-check="$notFailingAlertCheck" />
        @endforeach
    </x-slot:more-rows>
</x-compass::list>
