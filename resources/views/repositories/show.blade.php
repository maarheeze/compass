<x-compass::layouts.app :title="$repository->name">
    <main class="mx-auto max-w-5xl space-y-6 p-6">
        <x-compass::navigation :user="$user" />

        <h1 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $repository->name }}</h1>

        <section class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-zinc-950/5 dark:bg-zinc-900 dark:ring-white/10">
            <dl class="grid grid-cols-[max-content_1fr] gap-x-6 gap-y-2 text-sm">
                <dt class="text-zinc-500 dark:text-zinc-400">Visibility</dt>
                <dd class="lowercase">{{ $repository->visibility->value }}</dd>
                <dt class="text-zinc-500 dark:text-zinc-400">Branch</dt>
                <dd>{{ $repository->default_branch }}</dd>
                <dt class="text-zinc-500 dark:text-zinc-400">Last commit</dt>
                <dd><code>{{ $repository->last_commit_sha }}</code></dd>
                <dt class="text-zinc-500 dark:text-zinc-400">composer.json</dt>
                <dd><code>{{ $repository->composer_json_oid }}</code></dd>
                <dt class="text-zinc-500 dark:text-zinc-400">composer.lock</dt>
                <dd><code>{{ $repository->composer_lock_oid }}</code></dd>
                <dt class="text-zinc-500 dark:text-zinc-400">Active</dt>
                <dd>
                    @if ($repository->is_active)
                        Yes
                    @else
                        No
                    @endif
                </dd>
                <dt class="text-zinc-500 dark:text-zinc-400">Collected at</dt>
                <dd>
                    <x-compass::time :time="$repository->collected_at" />
                </dd>
            </dl>
        </section>

        <section class="space-y-3">
            <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">composer.json</h2>

            @if ($repository->composerJson)
                <dl class="grid grid-cols-[max-content_1fr] gap-x-6 gap-y-2 text-sm">
                    <dt class="text-zinc-500 dark:text-zinc-400">Type</dt>
                    <dd>{{ $repository->composerJson->type }}</dd>
                    <dt class="text-zinc-500 dark:text-zinc-400">PHP</dt>
                    <dd>{{ $repository->composerJson->php_constraint }}</dd>
                </dl>

                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-zinc-950/5 dark:bg-zinc-900 dark:ring-white/10">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-zinc-50 text-xs text-zinc-500 dark:bg-zinc-800/50 dark:text-zinc-400">
                            <tr>
                                <th class="px-4 py-2 font-medium">Package</th>
                                <th class="px-4 py-2 font-medium">Constraint</th>
                                <th class="px-4 py-2 font-medium">Section</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-white/5">
                            @foreach ($repository->composerJson->packages as $package)
                                <tr>
                                    <td class="px-4 py-2 text-zinc-900 dark:text-zinc-100">{{ $package->name }}</td>
                                    <td class="px-4 py-2 text-zinc-500 dark:text-zinc-400">{{ $package->constraint }}</td>
                                    <td class="px-4 py-2 text-zinc-500 lowercase dark:text-zinc-400">{{ $package->section->value }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-zinc-500 dark:text-zinc-400">No composer.json collected.</p>
            @endif
        </section>

        <section class="space-y-3">
            <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">composer.lock</h2>

            @if ($repository->composerLock)
                <dl class="grid grid-cols-[max-content_1fr] gap-x-6 gap-y-2 text-sm">
                    <dt class="text-zinc-500 dark:text-zinc-400">PHP</dt>
                    <dd>{{ $repository->composerLock->php_constraint }}</dd>
                </dl>

                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-zinc-950/5 dark:bg-zinc-900 dark:ring-white/10">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-zinc-50 text-xs text-zinc-500 dark:bg-zinc-800/50 dark:text-zinc-400">
                            <tr>
                                <th class="px-4 py-2 font-medium">Package</th>
                                <th class="px-4 py-2 font-medium">Version</th>
                                <th class="px-4 py-2 font-medium">Section</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-white/5">
                            @foreach ($repository->composerLock->packages as $package)
                                <tr>
                                    <td class="px-4 py-2 text-zinc-900 dark:text-zinc-100">{{ $package->name }}</td>
                                    <td class="px-4 py-2 text-zinc-500 dark:text-zinc-400">{{ $package->version }}</td>
                                    <td class="px-4 py-2 text-zinc-500 lowercase dark:text-zinc-400">{{ $package->section->value }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-zinc-500 dark:text-zinc-400">No composer.lock collected.</p>
            @endif
        </section>
    </main>
</x-compass::layouts.app>
