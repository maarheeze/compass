<div class="overflow-hidden rounded-md border border-border bg-canvas">
    <table class="w-full table-fixed text-left text-sm">
        <thead class="bg-canvas-subtle text-xs text-foreground-muted">
            <tr>
                <th class="w-48 px-4 py-2 font-medium">{{ __('compass::alert.alert') }}</th>
                <th class="px-4 py-2 font-medium">{{ __('compass::alert.subject') }}</th>
                <th class="w-28 px-4 py-2 font-medium">{{ __('compass::alert.state') }}</th>
                <th class="w-44 px-4 py-2 font-medium">{{ __('compass::alert.open_since') }}</th>
                <th class="w-44 px-4 py-2 font-medium">{{ __('compass::alert.resolved_since') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse ($alerts as $alert)
                <x-compass::alert-row :alert="$alert" />
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-foreground-muted">{{ __('compass::alert.no_alerts') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
