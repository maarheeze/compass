<x-compass::layouts.app :title="$site->name">
    <x-compass::navigation :section="\Maarheeze\Compass\View\NavigationSection::SITES" />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <div class="space-y-1">
            <h1 class="text-lg font-semibold text-foreground">{{ $site->name }}</h1>
            <p class="text-sm text-foreground-muted">Client settings. Leave a field empty to use the default.</p>
        </div>

        <form method="POST" action="{{ route(\Maarheeze\Compass\Http\RouteName::FORGE_SITE_CLIENT_UPDATE, $site) }}" class="space-y-4 rounded-md border border-border bg-canvas px-4 py-3 text-sm">
            @csrf
            @method('PATCH')

            <div class="space-y-1">
                <label for="client_path" class="block font-medium text-foreground">Path</label>
                <input
                    id="client_path"
                    name="client_path"
                    type="text"
                    value="{{ $clientPath }}"
                    placeholder="Default path"
                    class="w-full rounded-md border border-border bg-canvas px-2 py-1 font-mono text-sm text-foreground" />
                <p class="text-xs text-foreground-muted">Starts with a /.</p>
                @error('client_path')
                    <p class="text-xs text-red-700 dark:text-red-300">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1">
                <label for="client_key" class="block font-medium text-foreground">Key</label>
                <input
                    id="client_key"
                    name="client_key"
                    type="text"
                    value="{{ $clientKey }}"
                    placeholder="Default key"
                    class="w-full rounded-md border border-border bg-canvas px-2 py-1 font-mono text-sm text-foreground" />
                @error('client_key')
                    <p class="text-xs text-red-700 dark:text-red-300">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-2">
                <x-compass::button>Save</x-compass::button>
                <a href="{{ route(\Maarheeze\Compass\Http\RouteName::FORGE_SITE, $site) }}" class="text-foreground-muted hover:underline">Cancel</a>
            </div>
        </form>
    </main>
</x-compass::layouts.app>
