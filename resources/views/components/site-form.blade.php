<form method="POST" action="{{ $updateUrl }}" class="space-y-4 rounded-md border border-border bg-canvas px-4 py-3 text-sm">
    @csrf
    @method('PATCH')

    <div class="space-y-1">
        <label for="key" class="block font-medium text-foreground">{{ __('compass::site.key') }}</label>
        <input
            id="key"
            name="key"
            type="text"
            value="{{ $key }}"
            class="w-full rounded-md border border-border bg-canvas px-2 py-1 font-mono text-sm text-foreground" />
        <p class="text-xs text-foreground-muted">{{ __('compass::site.key_hint') }}</p>
        @error('key')
            <p class="text-xs text-red-700 dark:text-red-300">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center gap-2">
        <x-compass::button>{{ __('compass::site.save') }}</x-compass::button>
        <a href="{{ $cancelUrl }}" class="text-foreground-muted hover:underline">{{ __('compass::site.cancel') }}</a>
    </div>
</form>
