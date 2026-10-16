<x-compass::layouts.app :title="__('Dashboard')">
    <main class="mx-auto max-w-5xl space-y-6 p-6">
        <header class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if (auth()->user()->avatar_url)
                    <img src="{{ auth()->user()->avatar_url }}" alt="" class="size-9 rounded-full">
                @endif
                <div>
                    <h1 class="text-sm font-semibold text-zinc-900">{{ auth()->user()->name ?? auth()->user()->nickname }}</h1>
                    <p class="text-xs text-zinc-500">{{ '@'.auth()->user()->nickname }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route(\Maarheeze\Compass\Http\RouteName::LOGOUT) }}">
                @csrf
                <button type="submit" class="text-sm text-zinc-500 hover:text-zinc-900">Sign out</button>
            </form>
        </header>

        <p class="text-sm text-zinc-500">Signed in. Repository and deployment status will appear here.</p>
    </main>
</x-compass::layouts.app>
