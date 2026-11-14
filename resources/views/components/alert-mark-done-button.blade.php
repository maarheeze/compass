<form method="POST" action="{{ $markDoneUrl }}" {{ $attributes }}>
    @csrf
    {{ $slot }}
</form>
