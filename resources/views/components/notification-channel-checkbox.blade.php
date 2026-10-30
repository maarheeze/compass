<label class="flex cursor-pointer items-center gap-2 text-foreground-muted">
    <input
        type="checkbox"
        name="notification_channels[{{ $alertType->value }}][]"
        value="{{ $notificationChannel->value }}"
        @checked($isChosen)
        class="size-4 cursor-pointer" />
    <x-compass::enum-label :value="$notificationChannel" />
</label>
