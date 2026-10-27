@props(['siteEndpointCheck'])

@if ($siteEndpointCheck->is_successful)
    <x-compass::badge type="success">{{ __('compass::endpoint-check.ok') }}</x-compass::badge>
@else
    <x-compass::badge type="danger">{{ __('compass::endpoint-check.not_ok') }}</x-compass::badge>
@endif
