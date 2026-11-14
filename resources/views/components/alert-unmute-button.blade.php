<form method="POST" action="{{ $unmuteUrl }}" {{ $attributes }}>
    @csrf
    {{ $slot }}
</form>
