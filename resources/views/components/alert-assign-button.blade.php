<form method="POST" action="{{ $assignUrl }}" {{ $attributes }}>
    @csrf
    <x-compass::button>{{ __('compass::alert.assign_to_me') }}</x-compass::button>
</form>
