@props(['sourceUrl'])

@if ($sourceUrl !== null)
    <x-compass::link-button href="{{ $sourceUrl }}" target="_blank" rel="noopener" {{ $attributes }}>{{ __('compass::source.page_link') }}</x-compass::link-button>
@endif
