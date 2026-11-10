<div data-compass-toast-stack class="pointer-events-none fixed inset-x-0 top-4 z-50 mx-auto flex w-full max-w-sm flex-col gap-2 px-4">
    @if ($confirmedEntryType !== null)
        <x-compass::toast type="neutral">
            <x-compass::enum-label :value="$confirmedEntryType" />
        </x-compass::toast>
    @endif
    @foreach ($runningRuns as $runningRun)
        <x-compass::run-toast :run="$runningRun" />
    @endforeach
</div>
