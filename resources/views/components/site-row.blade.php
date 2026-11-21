<tr>
    <td class="px-4 py-3">
        <x-compass::site-title :site="$site">
            <a href="{{ $url }}" class="truncate text-base font-semibold text-foreground hover:text-accent">{{ $site->name }}</a>
        </x-compass::site-title>
        <div class="mt-1 flex flex-wrap items-center gap-x-2 text-xs text-foreground-muted">
            <span title="{{ $site->server_ip_address }}">{{ $site->server_name }}</span>
            @if ($site->php_version)
                <span>&middot;</span>
                <span>{{ $site->php_version }}</span>
            @endif
            @if ($site->repository)
                <span>&middot;</span>
                <span>{{ $site->repository->name }}</span>
            @endif
            @if ($site->latestDeploymentTime() !== null)
                <span>&middot;</span>
                <span>
                    {{ __('compass::site.latest_deployment_time') }}
                    <x-compass::time :time="$site->latestDeploymentTime()" />
                </span>
            @endif
        </div>
    </td>
</tr>
