<tr>
    <td class="px-4 py-2 font-medium">
        <a href="{{ $url }}" class="text-accent hover:underline">
            <x-compass::enum-label :value="$type" />
        </a>
    </td>
    <td class="px-4 py-2 text-foreground">{{ $description }}</td>
    <td class="px-4 py-2 text-foreground-muted">
        <x-compass::date-time :time="$createdAt" />
    </td>
    {{ $slot }}
</tr>
