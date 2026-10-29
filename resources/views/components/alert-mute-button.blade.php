<form method="POST" action="{{ $muteUrl }}" {{ $attributes }}>
    @csrf
    <x-compass::button
        title="{{ __('compass::alert.mute') }}"
        aria-label="{{ __('compass::alert.mute') }}">
        <x-compass::icons.bell-slash class="text-foreground-muted" />
    </x-compass::button>
</form>
