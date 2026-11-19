<x-compass::list>
    <x-slot:icon>
        <x-dynamic-component :component="$iconComponent" />
    </x-slot:icon>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:empty>{{ __('compass::repository.none') }}</x-slot:empty>
    <x-slot:counts>
        @if ($repositoryGroup->repositories->isNotEmpty())
            <x-compass::badge type="neutral">{{ trans_choice('compass::repository.repository_count', $repositoryGroup->repositories->count()) }}</x-compass::badge>
        @endif
    </x-slot:counts>
    <x-slot:rows>
        @foreach ($repositoryGroup->repositories as $repository)
            <x-compass::repository-row :repository="$repository" />
        @endforeach
    </x-slot:rows>
</x-compass::list>
