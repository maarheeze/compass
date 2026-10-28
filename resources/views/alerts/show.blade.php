<x-compass::layouts.app :title="$description">
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <div class="flex items-start gap-3">
            <div class="space-y-1">
                <h1 class="text-lg font-semibold text-foreground">
                    <x-compass::enum-label :value="$alert->type" />
                </h1>
                <p class="text-sm text-foreground-muted">{{ $description }}</p>
            </div>
            <x-compass::alert-mark-done-button :alert="$alert" class="ml-auto" />
        </div>

        <div class="rounded-md border border-border bg-canvas px-4 py-3 text-sm">
            <dl class="grid grid-cols-[max-content_1fr] gap-x-6 gap-y-2">
                <dt class="text-foreground-muted">{{ __('compass::alert.condition') }}</dt>
                <dd class="text-foreground">
                    <x-compass::enum-label :value="$alert->condition" />
                </dd>

                <dt class="text-foreground-muted">{{ __('compass::alert.open_since') }}</dt>
                <dd class="text-foreground">
                    <x-compass::date-time :time="$alert->created_at" />
                </dd>

                <dt class="text-foreground-muted">{{ __('compass::alert.status') }}</dt>
                <dd class="text-foreground">
                    <x-compass::enum-label :value="$alert->status" />
                </dd>
            </dl>
        </div>

        <section class="space-y-3">
            <x-compass::section-heading>
                <x-slot:icon>
                    <x-compass::icons.history />
                </x-slot:icon>
                {{ __('compass::alert.history') }}
            </x-compass::section-heading>
            <div class="overflow-hidden rounded-md border border-border bg-canvas">
                <table class="w-full table-fixed text-left text-sm">
                    <thead class="bg-canvas-subtle text-xs text-foreground-muted">
                        <tr>
                            <th class="px-4 py-2 font-medium">{{ __('compass::alert.alert') }}</th>
                            <th class="w-88 px-4 py-2 font-medium">{{ __('compass::alert.occurred_at') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($historyEntries as $historyEntry)
                            <tr>
                                <td class="px-4 py-2 text-foreground">
                                    <x-compass::enum-label :value="$historyEntry->type" />
                                </td>
                                <td class="px-4 py-2 text-foreground-muted">
                                    <x-compass::date-time :time="$historyEntry->occurred_at" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</x-compass::layouts.app>
