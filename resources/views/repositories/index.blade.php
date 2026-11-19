<x-compass::layouts.app>
    <x-slot:title>{{ __('compass::navigation.repositories') }}</x-slot:title>
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl px-4 py-6 md:px-6 lg:px-8">
        <x-compass::page-columns>
            @foreach ($repositoryGroups as $repositoryGroup)
                <x-compass::repository-group-list :repository-group="$repositoryGroup" />
            @endforeach

            <x-slot:aside>
                <x-compass::filter-section :filter="$kindFilter" />
                <x-compass::filter-section :filter="$visibilityFilter" />
            </x-slot:aside>
        </x-compass::page-columns>
    </main>
</x-compass::layouts.app>
