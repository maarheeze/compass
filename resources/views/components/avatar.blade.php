@if ($url !== null)
    <a href="{{ $url }}">
        <x-compass::avatar-image :person="$person" {{ $attributes }} />
    </a>
@else
    <x-compass::avatar-image :person="$person" {{ $attributes }} />
@endif
