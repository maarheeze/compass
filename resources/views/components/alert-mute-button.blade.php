<form method="POST" action="{{ $muteUrl }}" {{ $attributes }}>
    @csrf
    {{ $slot }}
</form>
