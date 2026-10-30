@props(['repositories'])

<div class="overflow-hidden rounded-md border border-border bg-canvas">
    <table class="w-full table-fixed text-left text-sm">
        <tbody class="divide-y divide-border">
            @forelse ($repositories as $repository)
                <x-compass::repository-row :repository="$repository" />
            @empty
                <tr>
                    <td class="px-4 py-6 text-center text-foreground-muted">{{ __('compass::repository.no_repositories') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
