<form method="POST" action="{{ $markDoneUrl }}" {{ $attributes }}>
    @csrf
    <x-compass::button>{{ __('compass::alert.mark_done') }}</x-compass::button>
</form>
