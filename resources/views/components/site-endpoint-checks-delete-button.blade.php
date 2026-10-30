<form method="POST" action="{{ $deleteUrl }}" {{ $attributes }}>
    @csrf
    @method('DELETE')
    <x-compass::button>{{ __('compass::endpoint-check.delete') }}</x-compass::button>
</form>
