<tr>
    <td class="px-4 py-3">
        <div class="font-semibold text-foreground">
            <x-compass::alert-type-label :identifier="$alertCheck->alert_type_identifier" />
        </div>
        <div class="mt-0.5 text-xs text-foreground-muted">
            {{ __('compass::alert-check.checked') }}
            <x-compass::time :time="$alertCheck->observed_at" />
        </div>
    </td>
    <td class="w-28 px-4 py-3">
        <x-compass::alert-check-state-badge :alert-check="$alertCheck" />
    </td>
</tr>
