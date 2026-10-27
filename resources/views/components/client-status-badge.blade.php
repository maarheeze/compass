<x-compass::badge :type="$badgeType()->value">
    @if ($status === null)
        {{ __('compass::client.not_checked') }}
    @else
        <x-compass::enum-label :value="$status" />
    @endif
</x-compass::badge>
