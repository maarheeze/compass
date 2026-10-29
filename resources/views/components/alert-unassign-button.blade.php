<form method="POST" action="{{ $unassignUrl }}" {{ $attributes }}>
    @csrf
    <x-compass::button>{{ __('compass::alert.unassign') }}</x-compass::button>
</form>
