<tr>
    <td class="px-4 py-2 font-medium text-foreground">
        <x-compass::enum-label :value="$type" />
    </td>
    <td class="px-4 py-2 text-foreground">{{ $description }}</td>
    <td class="px-4 py-2 text-foreground-muted">
        <x-compass::date-time :time="$createdAt" />
    </td>
    {{ $slot }}
</tr>
