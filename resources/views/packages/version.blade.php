<x-compass::layouts.app>
    <x-slot:title>{{ $packageName }} {{ $version }}</x-slot:title>
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <h1 class="text-lg font-semibold text-foreground">{{ $packageName }} {{ $version }}</h1>

        <x-compass::package-projects :locked-packages="$lockedPackages" />
    </main>
</x-compass::layouts.app>
