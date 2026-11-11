<x-compass::layouts.app>
    <x-slot:title>
        <x-compass::alert-subject-name :alert-subject="$alert->subject" />
    </x-slot:title>
    <x-compass::navigation />

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-6 md:px-6 lg:px-8">
        <div class="flex items-start gap-3">
            <div class="space-y-1">
                <h1 class="text-lg font-semibold text-foreground">
                    <x-compass::alert-type-label :identifier="$alert->alert_type_identifier" />
                </h1>
                <p class="text-sm">
                    <x-compass::alert-subject-link :alert-subject="$alert->subject" />
                </p>
            </div>
            <div class="ml-auto space-y-1">
                <div class="flex justify-end gap-2">
                    <x-compass::alert-assign-button :alert="$alert" />
                    <x-compass::alert-unassign-button :alert="$alert" />
                    <x-compass::alert-mute-button :alert="$alert" />
                    <x-compass::alert-unmute-button :alert="$alert" />
                    <x-compass::alert-mark-done-button :alert="$alert" />
                    <x-compass::alert-reopen-button :alert="$alert" />
                </div>
                @error('alert')
                    <p class="text-right text-xs text-red-700 dark:text-red-300">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div data-compass-live-region="alert-details" class="rounded-md border border-border bg-canvas px-4 py-3 text-sm">
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
                        <x-compass::avatar
                            :name="$alert->assignee->nickname"
                            :image-url="$alert->assignee->avatar_url"
                            :url="null"
                            class="size-5"
                        />
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
            <div data-compass-live-region="alert-history" class="overflow-hidden rounded-md border border-border bg-canvas">
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
                                        <x-compass::avatar
                                            :name="$historyEntry->user->nickname"
                                            :image-url="$historyEntry->user->avatar_url"
                                            :url="null"
                                            class="ml-auto block size-5"
                                        />
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
