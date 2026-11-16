<x-compass::notice type="warning">
    <x-compass::icons.alert />
    <span>
        {{ __('compass::missing.missing_from_last_run_since') }}
        <x-compass::time :time="$missingAt" />
    </span>

    <div class="ml-auto flex items-center gap-2">
        @if ($canIgnore)
            <form method="POST" action="{{ $ignoreUrl }}">
                @csrf
                <x-compass::button>{{ __('compass::missing.ignore') }}</x-compass::button>
            </form>
        @endif

        <form method="POST" action="{{ $deleteUrl }}">
            @csrf
            @method('DELETE')
            <x-compass::button>{{ __('compass::missing.delete') }}</x-compass::button>
        </form>
    </div>
</x-compass::notice>
