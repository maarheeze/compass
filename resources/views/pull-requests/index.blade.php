<x-compass::layouts.app>
    <x-slot:title>{{ __('compass::navigation.pull_requests') }}</x-slot:title>
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl px-4 py-6 md:px-6 lg:px-8">
        <x-compass::page-columns>
            <x-compass::pull-request-list :pull-requests="$pullRequests" />

            <x-slot:aside>
                <x-compass::filter-section :filter="$assigneesFilter" />
            </x-slot:aside>
        </x-compass::page-columns>
    </main>
</x-compass::layouts.app>
