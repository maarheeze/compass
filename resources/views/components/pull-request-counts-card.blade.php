<x-compass::assigned-and-open-counts
    :title="__('compass::navigation.pull_requests')"
    :assigned-url="$assignedUrl"
    :assigned-count="$pullRequestCounts->assignedPullRequestCount"
    :open-url="$openUrl"
    :open-count="$pullRequestCounts->openPullRequestCount">
    <x-slot:icon>
        <x-compass::icons.git-pull-request />
    </x-slot:icon>
</x-compass::assigned-and-open-counts>
