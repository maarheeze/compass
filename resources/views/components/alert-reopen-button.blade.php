<form method="POST" action="{{ $reopenUrl }}" {{ $attributes }}>
    @csrf
    <x-compass::button>{{ __('compass::alert.reopen') }}</x-compass::button>
</form>
