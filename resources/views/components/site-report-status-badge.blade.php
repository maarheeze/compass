<x-compass::badge :type="$badgeType()->value">
    @if ($status === null)
        {{ __('compass::site-report.not_checked') }}
    @else
        <x-compass::enum-label :value="$status" />
    @endif
</x-compass::badge>
