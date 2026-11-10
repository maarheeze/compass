<div data-compass-toast-stack class="pointer-events-none fixed top-4 right-0 z-50 flex w-full max-w-sm flex-col gap-2 px-4">
    @if ($confirmedEntryType !== null)
        <x-compass::toast type="neutral">
            <x-compass::enum-label :value="$confirmedEntryType" />
        </x-compass::toast>
    @endif
    @foreach ($runningRuns as $runningRun)
        <x-compass::run-toast :run="$runningRun" />
    @endforeach
</div>
