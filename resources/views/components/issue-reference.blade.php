@if ($url !== null)
    <a href="{{ $url }}" {{ $attributes->merge(['class' => 'hover:text-accent']) }}>{{ $reference }}</a>
@else
    <span {{ $attributes }}>{{ $reference }}</span>
@endif
