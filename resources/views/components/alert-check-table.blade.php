<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-md border border-border bg-canvas']) }}>
    <table class="w-full table-fixed text-left text-sm">
        <tbody class="divide-y divide-border">
            @forelse ($alertChecks as $alertCheck)
                <x-compass::alert-check-row :alert-check="$alertCheck" />
            @empty
                <tr>
                    <td colspan="2" class="px-4 py-6 text-center text-foreground-muted">{{ __('compass::alert-check.no_checks') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
