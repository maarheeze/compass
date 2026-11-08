<tr>
    <td class="px-4 py-3">
        <a href="{{ $url }}" class="text-base font-semibold text-foreground hover:text-accent">
            <x-compass::alert-type-label :alert="$alert" />
        </a>
        <div class="mt-0.5 text-xs text-foreground-muted">
            <x-compass::alert-subject-link :subject="$alert->subject" />
            ·
            {{ __('compass::alert.open_since') }}
            <x-compass::date-time :time="$alert->created_at" />
            @if ($alert->resolved_at)
                ·
                {{ __('compass::alert.resolved_since') }}
                <x-compass::date-time :time="$alert->resolved_at" />
            @endif
        </div>
    </td>
    <td class="w-28 px-4 py-3">
        <x-compass::alert-state-badge :alert="$alert" />
    </td>
    <td class="w-10 py-3 pl-4 pr-0">
        @if ($alert->assignee)
            <x-compass::avatar :person="$alert->assignee" :url="null" class="size-6" />
        @endif
    </td>
    <td class="w-11 py-3 pl-2 pr-4">
        @if ($alert->is_muted)
            <span
                title="{{ __('compass::alert.muted') }}"
                aria-label="{{ __('compass::alert.muted') }}"
                class="flex text-foreground-muted">
                <x-compass::icons.bell-slash class="size-5" />
            </span>
        @endif
    </td>
</tr>
