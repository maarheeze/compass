@props(['pullRequests'])

<x-compass::list>
    <x-slot:icon>
        <x-compass::icons.git-pull-request />
    </x-slot:icon>
    <x-slot:title>{{ __('compass::navigation.pull_requests') }}</x-slot:title>
    <x-slot:empty>{{ __('compass::pull-request.none') }}</x-slot:empty>
    <x-slot:counts>
        @if ($pullRequests->isNotEmpty())
            <x-compass::badge type="neutral">{{ __('compass::pull-request.open_count', ['count' => $pullRequests->count()]) }}</x-compass::badge>
        @endif
    </x-slot:counts>
    <x-slot:rows>
        @foreach ($pullRequests as $pullRequest)
            <x-compass::pull-request-row :pull-request="$pullRequest" />
        @endforeach
    </x-slot:rows>
</x-compass::list>
