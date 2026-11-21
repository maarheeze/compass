@props(['sourceUrl'])

@if ($sourceUrl !== null)
    <a href="{{ $sourceUrl }}" target="_blank" rel="noopener" {{ $attributes->merge(['class' => 'text-sm text-foreground-muted hover:underline']) }}>{{ __('compass::source.page_link') }}</a>
@endif
