@if ($url !== null)
    <a href="{{ $url }}">
        <x-compass::avatar-image :name="$name" :image-url="$imageUrl" {{ $attributes }} />
    </a>
@else
    <x-compass::avatar-image :name="$name" :image-url="$imageUrl" {{ $attributes }} />
@endif
