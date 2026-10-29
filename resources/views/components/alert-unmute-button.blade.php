<form method="POST" action="{{ $unmuteUrl }}" {{ $attributes }}>
    @csrf
    <x-compass::button>{{ __('compass::alert.unmute') }}</x-compass::button>
</form>
