<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-md border border-border bg-canvas']) }}>
    <table class="w-full table-fixed text-left text-sm">
        <tbody class="divide-y divide-border">
            @forelse ($alerts as $alert)
                <x-compass::alert-row :alert="$alert" />
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-foreground-muted">{{ __('compass::alert.no_alerts') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
