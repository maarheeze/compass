<form method="POST" action="{{ $unmuteUrl }}" {{ $attributes }}>
    @csrf
    <x-compass::button
        title="{{ __('compass::alert.unmute') }}"
        aria-label="{{ __('compass::alert.unmute') }}">
        <x-compass::icons.bell class="text-foreground-muted" />
    </x-compass::button>
</form>
