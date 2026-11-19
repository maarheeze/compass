<tr class="group relative isolate focus-within:z-10 hover:z-10">
    <td class="px-4 py-2">
        <div class="text-xs text-foreground-muted">
            <x-compass::alert-subject-link :alert-subject="$alert->subject" class="relative" />
        </div>
        <a
            href="{{ $url }}"
            class="text-sm text-foreground before:absolute before:inset-0 before:-z-10 after:absolute after:inset-0 group-last:before:rounded-b-md group-focus-within:before:bg-accent-muted group-focus-within:before:shadow-[inset_2px_0_0_var(--color-accent)] group-hover:before:bg-accent-muted group-hover:before:shadow-[inset_2px_0_0_var(--color-accent)]">
            <x-compass::alert-type-label :identifier="$alert->alert_type_identifier" />
        </a>
    </td>
    <td class="w-28 px-4 py-2">
        <x-compass::alert-level-badge :identifier="$alert->alert_type_identifier" />
    </td>
    <td class="w-28 px-4 py-2">
        <x-compass::alert-state-badge :alert="$alert" />
    </td>
    <td class="w-10 py-2 pl-4 pr-0">
        @if ($alert->assignee)
            <x-compass::avatar
                :name="$alert->assignee->nickname"
                :image-url="$alert->assignee->avatar_url"
                :url="null"
                class="size-6"
            />
        @endif
    </td>
    <td class="w-9 py-2 pl-4 pr-0">
        @if ($alert->is_muted)
            <span
                title="{{ __('compass::alert.muted') }}"
                aria-label="{{ __('compass::alert.muted') }}"
                class="flex text-foreground-muted">
                <x-compass::icons.bell-slash class="size-5" />
            </span>
        @endif
    </td>
    <td class="relative w-40 px-4 py-2 text-right text-xs text-foreground-muted">
        <span class="group-focus-within:invisible group-hover:invisible">
            <x-compass::relative-time :time="$stateSince()" />
        </span>
        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center gap-2 pr-2 opacity-0 group-focus-within:pointer-events-auto group-focus-within:opacity-100 group-hover:pointer-events-auto group-hover:opacity-100">
            <x-compass::alert-assign-button :alert="$alert" data-compass-sends-without-refresh>
                <x-compass::icon-button :label="__('compass::alert.assign_to_me')">
                    <x-compass::icons.person-add />
                </x-compass::icon-button>
            </x-compass::alert-assign-button>
            <x-compass::alert-unassign-button :alert="$alert" data-compass-sends-without-refresh>
                <x-compass::icon-button :label="__('compass::alert.unassign')">
                    <x-compass::icons.person-slash />
                </x-compass::icon-button>
            </x-compass::alert-unassign-button>
            <x-compass::alert-mute-button :alert="$alert" data-compass-sends-without-refresh>
                <x-compass::icon-button :label="__('compass::alert.mute')">
                    <x-compass::icons.bell-slash />
                </x-compass::icon-button>
            </x-compass::alert-mute-button>
            <x-compass::alert-unmute-button :alert="$alert" data-compass-sends-without-refresh>
                <x-compass::icon-button :label="__('compass::alert.unmute')">
                    <x-compass::icons.bell />
                </x-compass::icon-button>
            </x-compass::alert-unmute-button>
            <x-compass::alert-mark-done-button :alert="$alert" data-compass-sends-without-refresh>
                <x-compass::icon-button :label="__('compass::alert.mark_done')">
                    <x-compass::icons.check />
                </x-compass::icon-button>
            </x-compass::alert-mark-done-button>
            <x-compass::alert-reopen-button :alert="$alert" data-compass-sends-without-refresh>
                <x-compass::icon-button :label="__('compass::alert.reopen')">
                    <x-compass::icons.issue-reopened />
                </x-compass::icon-button>
            </x-compass::alert-reopen-button>
        </div>
    </td>
</tr>
