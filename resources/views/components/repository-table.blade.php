@props(['repositories'])

<div class="overflow-hidden rounded-md border border-border bg-canvas">
    <table class="w-full table-fixed text-left text-sm">
        <thead class="bg-canvas-subtle text-xs text-foreground-muted">
            <tr>
                <th class="px-4 py-2 font-medium">{{ __('compass::repository.repository') }}</th>
                <th class="w-24 px-4 py-2 font-medium">{{ __('compass::repository.visibility') }}</th>
                <th class="w-28 px-4 py-2 font-medium">{{ __('compass::repository.branch') }}</th>
                <th class="w-28 px-4 py-2 font-medium">{{ __('compass::repository.last_commit') }}</th>
                <th class="w-24 px-4 py-2 font-medium">PHP</th>
                <th class="w-48 px-4 py-2 font-medium">{{ __('compass::repository.collected_at') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse ($repositories as $repository)
                <tr>
                    <td class="px-4 py-2 font-medium text-foreground">
                        <div class="flex items-center gap-2">
                            <x-compass::repository-link :repository="$repository" class="truncate" />
                            <x-compass::missing-label :missing-at="$repository->missing_at" :missing-ignored-at="$repository->missing_ignored_at" />
                            <x-compass::alert-icon :subject="$repository" />
                        </div>
                    </td>
                    <td class="px-4 py-2 text-foreground-muted lowercase">
                        <x-compass::enum-label :value="$repository->visibility" />
                    </td>
                    <td class="truncate px-4 py-2 text-foreground-muted">{{ $repository->default_branch }}</td>
                    <td class="px-4 py-2 text-foreground-muted">
                        <x-compass::commit-hash :repository="$repository" :commit-hash="$repository->last_commit_sha" />
                    </td>
                    <td class="px-4 py-2 text-foreground-muted">{{ $repository->composerJson?->php_constraint }}</td>
                    <td class="px-4 py-2 text-foreground-muted">
                        <x-compass::time :time="$repository->collected_at" />
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-foreground-muted">{{ __('compass::repository.no_repositories') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
