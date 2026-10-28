<tr>
    <td class="px-4 py-2 font-medium">
        <a href="{{ $url }}" class="text-accent hover:underline">
            <x-compass::enum-label :value="$alert->type" />
        </a>
    </td>
    <td class="px-4 py-2">
        <x-compass::alert-subject-link :subject="$alert->subject" />
    </td>
    <td class="px-4 py-2">
        <x-compass::alert-state-badge :alert="$alert" />
    </td>
    <td class="px-4 py-2 text-foreground-muted">
        <x-compass::date-time :time="$alert->created_at" />
    </td>
    <td class="px-4 py-2 text-foreground-muted">
        @if ($alert->resolved_at)
            <x-compass::date-time :time="$alert->resolved_at" />
        @endif
    </td>
</tr>
