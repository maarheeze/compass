<x-compass::layouts.app>
    <x-slot:title>{{ __('compass::navigation.issues') }}</x-slot:title>
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <div class="overflow-hidden rounded-md border border-border bg-canvas">
            <table class="w-full table-fixed text-left text-sm">
                <tbody class="divide-y divide-border">
                    @forelse ($issues as $issue)
                        <x-compass::issue-row :issue="$issue" />
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-6 text-center text-foreground-muted">{{ __('compass::issue.no_open_issues') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</x-compass::layouts.app>
