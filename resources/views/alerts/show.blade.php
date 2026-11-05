<x-compass::layouts.app :title="$alert->subject->displayName()">
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <div class="flex items-start gap-3">
            <div class="space-y-1">
                <h1 class="text-lg font-semibold text-foreground">
                    <x-compass::enum-label :value="$alert->type" />
                </h1>
                <p class="text-sm">
                    <x-compass::alert-subject-link :subject="$alert->subject" />
                </p>
            </div>
            <div class="ml-auto flex gap-2">
                <x-compass::alert-assign-button :alert="$alert" />
                <x-compass::alert-unassign-button :alert="$alert" />
                <x-compass::alert-mute-button :alert="$alert" />
                <x-compass::alert-unmute-button :alert="$alert" />
                <x-compass::alert-mark-done-button :alert="$alert" />
                <x-compass::alert-reopen-button :alert="$alert" />
            </div>
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

                <dt class="text-foreground-muted">{{ __('compass::alert.assigned_to') }}</dt>
                <dd class="text-foreground">
                    @if ($alert->assignee)
                        <x-compass::user-avatar :user="$alert->assignee" class="size-5" />
                    @endif
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
                    <tbody class="divide-y divide-border">
                        @foreach ($historyEntries as $historyEntry)
                            <tr>
                                <td class="px-4 py-3">
                                    <span class="text-base font-semibold text-foreground">
                                        <x-compass::enum-label :value="$historyEntry->type" />
                                    </span>
                                    <div class="mt-1 text-xs text-foreground-muted">
                                        <x-compass::date-time :time="$historyEntry->occurred_at" />
                                    </div>
                                </td>
                                <td class="w-16 px-4 py-3">
                                    @if ($historyEntry->user)
                                        <x-compass::user-avatar :user="$historyEntry->user" class="ml-auto block size-5" />
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</x-compass::layouts.app>
