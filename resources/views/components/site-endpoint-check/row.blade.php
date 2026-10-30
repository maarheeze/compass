@props(['site', 'siteEndpointCheck'])

<tr>
    <td class="px-4 py-2">
        <div class="flex items-center gap-2">
            <x-compass::site-endpoint-checks-link :site="$site" :path="$siteEndpointCheck->path" />
            {{ $slot }}
        </div>
    </td>
    <td class="px-4 py-2">
        <x-compass::site-endpoint-check.status-label :site-endpoint-check="$siteEndpointCheck" />
    </td>
    <td class="px-4 py-2 text-foreground-muted">
        <x-compass::time :time="$siteEndpointCheck->created_at" />
    </td>
</tr>
