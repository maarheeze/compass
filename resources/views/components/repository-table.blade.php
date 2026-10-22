@props(['repositories'])

<div class="overflow-hidden rounded-md border border-border bg-canvas">
    <table class="w-full table-fixed text-left text-sm">
        <thead class="bg-canvas-subtle text-xs text-foreground-muted">
            <tr>
                <th class="px-4 py-2 font-medium">Repository</th>
                <th class="w-24 px-4 py-2 font-medium">Visibility</th>
                <th class="w-28 px-4 py-2 font-medium">Branch</th>
                <th class="w-28 px-4 py-2 font-medium">Last commit</th>
                <th class="w-24 px-4 py-2 font-medium">PHP</th>
                <th class="w-48 px-4 py-2 font-medium">Warning</th>
                <th class="w-48 px-4 py-2 font-medium">Collected at</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse ($repositories as $repository)
                <tr>
                    <td class="truncate px-4 py-2 font-medium text-foreground">
                        <a href="{{ route(\Maarheeze\Compass\Http\RouteName::GITHUB_REPOSITORY, $repository) }}" class="text-accent hover:underline">{{ $repository->name }}</a>
                    </td>
                    <td class="px-4 py-2 text-foreground-muted lowercase">{{ $repository->visibility->value }}</td>
                    <td class="truncate px-4 py-2 text-foreground-muted">{{ $repository->default_branch }}</td>
                    <td class="px-4 py-2 text-foreground-muted">
                        <code class="block w-[7ch] overflow-hidden" title="{{ $repository->last_commit_sha }}">{{ $repository->last_commit_sha }}</code>
                    </td>
                    <td class="px-4 py-2 text-foreground-muted">{{ $repository->composerJson?->php_constraint }}</td>
                    <td class="px-4 py-2">
                        <x-compass::composer.warning-badge :repository="$repository" />
                    </td>
                    <td class="px-4 py-2 text-foreground-muted">
                        <x-compass::time :time="$repository->collected_at" />
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-6 text-center text-foreground-muted">No repositories.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
