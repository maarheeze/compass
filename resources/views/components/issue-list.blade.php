@props(['issues'])

<x-compass::list>
    <x-slot:icon>
        <x-compass::icons.issue-opened />
    </x-slot:icon>
    <x-slot:title>{{ __('compass::navigation.issues') }}</x-slot:title>
    <x-slot:empty>{{ __('compass::issue.none') }}</x-slot:empty>
    <x-slot:counts>
        @if ($issues->isNotEmpty())
            <x-compass::badge type="neutral">{{ __('compass::issue.open_count', ['count' => $issues->count()]) }}</x-compass::badge>
        @endif
    </x-slot:counts>
    <x-slot:rows>
        @foreach ($issues as $issue)
            <x-compass::issue-row :issue="$issue" />
        @endforeach
    </x-slot:rows>
</x-compass::list>
