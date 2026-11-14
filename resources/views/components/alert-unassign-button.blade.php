<form method="POST" action="{{ $unassignUrl }}" {{ $attributes }}>
    @csrf
    {{ $slot }}
</form>
