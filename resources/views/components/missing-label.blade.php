@props(['missingAt', 'missingIgnoredAt'])

@if ($missingIgnoredAt)
    <x-compass::badge type="neutral">{{ __('compass::missing.ignored') }}</x-compass::badge>
@elseif ($missingAt)
    <x-compass::badge type="warning">{{ __('compass::missing.missing') }}</x-compass::badge>
@endif
