<form method="POST" action="{{ $inspectUrl }}" data-compass-sends-without-refresh {{ $attributes }}>
    @csrf
    <x-compass::primary-button>{{ __('compass::site.inspect_now') }}</x-compass::primary-button>
</form>
