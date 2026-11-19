<form method="POST" action="{{ $runUrl }}" data-compass-sends-without-refresh {{ $attributes }}>
    @csrf
    <input type="hidden" name="mutex_name" value="{{ $scheduledCommand->mutexName }}">
    <button type="submit" class="cursor-pointer text-accent hover:underline">{{ __('compass::run.run_now') }}</button>
</form>
