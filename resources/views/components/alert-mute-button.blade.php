<form method="POST" action="{{ $muteUrl }}" {{ $attributes }}>
    @csrf
    <x-compass::button>{{ __('compass::alert.mute') }}</x-compass::button>
</form>
