<x-compass::layouts.app :title="__('Dashboard')">
    <main class="mx-auto max-w-5xl space-y-6 p-6">
        <header class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if ($user->avatar_url)
                    <img src="{{ $user->avatar_url }}" alt="" class="size-9 rounded-full">
                @endif
                <div>
                    <h1 class="text-sm font-semibold text-zinc-900">
                        @if ($user->name)
                            {{ $user->name }}
                        @else
                            {{ $user->nickname }}
                        @endif
                    </h1>
                    <p class="text-xs text-zinc-500">&#64;{{ $user->nickname }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route(\Maarheeze\Compass\Http\RouteName::LOGOUT) }}">
                @csrf
                <button type="submit" class="text-sm text-zinc-500 hover:text-zinc-900">Sign out</button>
            </form>
        </header>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-zinc-950/5">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 text-xs text-zinc-500">
                    <tr>
                        <th class="px-4 py-2 font-medium">Repository</th>
                        <th class="px-4 py-2 font-medium">Branch</th>
                        <th class="px-4 py-2 font-medium">Collected at</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($repositories as $repository)
                        <tr>
                            <td class="px-4 py-2 font-medium text-zinc-900">{{ $repository->name }}</td>
                            <td class="px-4 py-2 text-zinc-500">{{ $repository->default_branch }}</td>
                            <td class="px-4 py-2 text-zinc-500">{{ $repository->collected_at }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-zinc-500">No repositories collected yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</x-compass::layouts.app>
