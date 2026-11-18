<div class="overflow-hidden rounded-md border border-border bg-canvas">
    <table class="w-full table-fixed text-left text-sm">
        <thead>
            <tr>
                <th class="bg-canvas-subtle px-4 py-2 font-semibold">
                    <a href="{{ $url }}" class="truncate text-foreground hover:text-accent">{{ $packageName }}</a>
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border border-t border-border">
            @forelse ($versionCounts as $versionCount)
                <x-compass::package-version-row :package-name="$packageName" :version-count="$versionCount" />
            @empty
                <tr>
                    <td class="px-4 py-2 text-foreground-muted">{{ __('compass::package.no_locking_project') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
