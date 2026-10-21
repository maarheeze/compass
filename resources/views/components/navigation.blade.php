@props(['user'])

<header class="flex items-center justify-between">
    <div class="flex items-center gap-6">
        <div class="flex items-center gap-3">
            @if ($user->avatar_url)
                <img src="{{ $user->avatar_url }}" alt="" class="size-9 rounded-full">
            @endif
            <div>
                <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                    @if ($user->name)
                        {{ $user->name }}
                    @else
                        {{ $user->nickname }}
                    @endif
                </p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">&#64;{{ $user->nickname }}</p>
            </div>
        </div>

        <nav class="flex items-center gap-4 text-sm font-medium text-zinc-600 dark:text-zinc-300">
            <a href="{{ route(\Maarheeze\Compass\Http\RouteName::DASHBOARD) }}" class="hover:text-zinc-900 dark:hover:text-white">Dashboard</a>
            <a href="{{ route(\Maarheeze\Compass\Http\RouteName::GITHUB_REPOSITORIES) }}" class="hover:text-zinc-900 dark:hover:text-white">GitHub</a>
        </nav>
    </div>

    <div class="flex items-center gap-2">
        <form method="POST" action="{{ route(\Maarheeze\Compass\Http\RouteName::GITHUB_SYNC) }}">
            @csrf
            <x-compass::button>Sync GitHub</x-compass::button>
        </form>

        <form method="POST" action="{{ route(\Maarheeze\Compass\Http\RouteName::LOGOUT) }}">
            @csrf
            <x-compass::button>Sign out</x-compass::button>
        </form>
    </div>
</header>
