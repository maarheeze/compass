@props(['missingAt', 'missingIgnoredAt', 'deleteUrl', 'ignoreUrl'])

<div class="flex flex-wrap items-center gap-3 rounded-md bg-amber-50 px-4 py-3 text-sm text-amber-700 ring-1 ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-400 dark:ring-amber-400/20">
    <x-compass::icons.alert />
    <span>
        Missing from the last sync since
        <x-compass::time :time="$missingAt" />
        @if ($missingIgnoredAt)
            &middot; ignored
        @endif
    </span>

    <div class="ml-auto flex items-center gap-2">
        @if (!$missingIgnoredAt)
            <form method="POST" action="{{ $ignoreUrl }}">
                @csrf
                <x-compass::button>Ignore</x-compass::button>
            </form>
        @endif

        <form method="POST" action="{{ $deleteUrl }}">
            @csrf
            @method('DELETE')
            <x-compass::button>Delete</x-compass::button>
        </form>
    </div>
</div>
