@if ($url !== null)
    <a href="{{ $url }}" {{ $attributes->merge(['class' => 'hover:text-accent']) }}>{{ $externalReference }}</a>
@else
    <span {{ $attributes }}>{{ $externalReference }}</span>
@endif
