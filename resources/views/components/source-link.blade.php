@props(['sourceUrl'])

@if ($sourceUrl !== null)
    <a href="{{ $sourceUrl }}" target="_blank" rel="noopener" {{ $attributes->merge(['class' => 'hover:text-accent']) }}>{{ $slot }}</a>
@else
    <span {{ $attributes }}>{{ $slot }}</span>
@endif
