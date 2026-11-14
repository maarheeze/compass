<form method="POST" action="{{ $assignUrl }}" {{ $attributes }}>
    @csrf
    {{ $slot }}
</form>
