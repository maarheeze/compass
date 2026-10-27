<div class="flex items-center gap-2">
    @if ($isSyncing)
        <span wire:poll.5s class="text-xs text-foreground-muted">{{ __('compass::navigation.syncing') }}</span>
    @elseif ($lastCollectedAt !== null)
        <a wire:poll.60s href="{{ $runsUrl }}" class="text-xs text-foreground-muted hover:underline">
            <x-compass::time :time="$lastCollectedAt" />
        </a>
    @endif

    <x-compass::button
        type="button"
        wire:click="sync"
        :disabled="$isSyncing"
        title="{{ __('compass::navigation.sync') }}"
        aria-label="{{ __('compass::navigation.sync') }}">
        <x-compass::icons.sync class="text-foreground-muted" />
    </x-compass::button>
</div>
