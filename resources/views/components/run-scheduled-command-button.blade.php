<form method="POST" action="{{ $runUrl }}" {{ $attributes }}>
    @csrf
    <input type="hidden" name="mutex_name" value="{{ $scheduledCommand->mutexName }}">
    <x-compass::button>{{ __('compass::run.run_now') }}</x-compass::button>
</form>
