<x-compass::assigned-and-open-counts
    :title="__('compass::navigation.issues')"
    :assigned-url="$assignedUrl"
    :assigned-count="$issueCounts->assignedIssueCount"
    :open-url="$openUrl"
    :open-count="$issueCounts->openIssueCount">
    <x-slot:icon>
        <x-compass::icons.issue-opened />
    </x-slot:icon>
</x-compass::assigned-and-open-counts>
