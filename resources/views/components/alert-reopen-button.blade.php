<form method="POST" action="{{ $reopenUrl }}" {{ $attributes }}>
    @csrf
    {{ $slot }}
</form>
