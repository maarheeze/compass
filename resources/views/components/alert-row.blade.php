<tr>
    <td class="px-4 py-2 font-medium text-foreground">
        <x-compass::enum-label :value="$type" />
    </td>
    <td class="px-4 py-2 text-foreground">{{ $description }}</td>
    <td class="px-4 py-2 text-foreground-muted">
        <x-compass::date-time :time="$createdAt" />
    </td>
    <td class="px-4 py-2 text-foreground-muted">
        @if ($isConditionPresent)
            {{ __('compass::alert.present') }}
        @else
            {{ __('compass::alert.gone_since') }}
            <x-compass::date-time :time="$goneAt" />
        @endif
    </td>
</tr>
