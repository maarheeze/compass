<tr>
    <td class="px-4 py-3">
        <x-compass::subject-name :subject="$site">
            <a href="{{ $url }}" class="truncate text-base font-semibold text-foreground hover:text-accent">{{ $site->name }}</a>
            <x-compass::forge.deployment-state-badge :site="$site" />
            @forelse ($site->reports as $siteReport)
                <x-compass::client-status-badge :status="$siteReport->status" />
            @empty
                <x-compass::client-status-badge :status="null" />
            @endforelse
        </x-compass::subject-name>
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
            @if ($site->deployment_ended_at)
                <span>&middot;</span>
                <span>
                    {{ __('compass::site.deployed') }}
                    <x-compass::time :time="$site->deployment_ended_at" />
                </span>
            @endif
        </div>
    </td>
</tr>
