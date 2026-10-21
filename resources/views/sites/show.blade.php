<x-compass::layouts.app :title="$site->name">
    <main class="mx-auto max-w-5xl space-y-6 p-6">
        <x-compass::navigation :user="$user" />

        <h1 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $site->name }}</h1>

        <section class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-zinc-950/5 dark:bg-zinc-900 dark:ring-white/10">
            <dl class="grid grid-cols-[max-content_1fr] gap-x-6 gap-y-2 text-sm">
                <dt class="text-zinc-500 dark:text-zinc-400">URL</dt>
                <dd>{{ $site->url }}</dd>
                <dt class="text-zinc-500 dark:text-zinc-400">Status</dt>
                <dd>{{ $site->status->value }}</dd>
                <dt class="text-zinc-500 dark:text-zinc-400">PHP</dt>
                <dd>{{ $site->php_version }}</dd>
                <dt class="text-zinc-500 dark:text-zinc-400">Repository</dt>
                <dd>{{ $site->repository_url }}</dd>
                <dt class="text-zinc-500 dark:text-zinc-400">Branch</dt>
                <dd>{{ $site->repository_branch }}</dd>
                <dt class="text-zinc-500 dark:text-zinc-400">Server</dt>
                <dd>{{ $site->server_name }}</dd>
                <dt class="text-zinc-500 dark:text-zinc-400">Server IP address</dt>
                <dd>{{ $site->server_ip_address }}</dd>
                <dt class="text-zinc-500 dark:text-zinc-400">Active</dt>
                <dd>
                    @if ($site->is_active)
                        Yes
                    @else
                        No
                    @endif
                </dd>
                <dt class="text-zinc-500 dark:text-zinc-400">Collected at</dt>
                <dd>
                    <x-compass::time :time="$site->collected_at" />
                </dd>
            </dl>
        </section>

        <section class="space-y-3">
            <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Latest deployment</h2>

            @if ($site->deployment_status)
                <dl class="grid grid-cols-[max-content_1fr] gap-x-6 gap-y-2 text-sm">
                    <dt class="text-zinc-500 dark:text-zinc-400">Status</dt>
                    <dd>{{ $site->deployment_status->value }}</dd>
                    <dt class="text-zinc-500 dark:text-zinc-400">Commit</dt>
                    <dd><code>{{ $site->deployed_commit_hash }}</code></dd>
                    <dt class="text-zinc-500 dark:text-zinc-400">Message</dt>
                    <dd>{{ $site->deployed_commit_message }}</dd>
                    <dt class="text-zinc-500 dark:text-zinc-400">Started at</dt>
                    <dd>
                        @if ($site->deployment_started_at)
                            <x-compass::time :time="$site->deployment_started_at" />
                        @endif
                    </dd>
                    <dt class="text-zinc-500 dark:text-zinc-400">Ended at</dt>
                    <dd>
                        @if ($site->deployment_ended_at)
                            <x-compass::time :time="$site->deployment_ended_at" />
                        @endif
                    </dd>
                </dl>
            @else
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Never deployed.</p>
            @endif
        </section>
    </main>
</x-compass::layouts.app>
