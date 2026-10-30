@props(['subject'])

<div class="flex min-w-0 items-center gap-2">
    {{ $slot }}
    <x-compass::missing-label :subject="$subject" />
    <x-compass::alert-icon :subject="$subject" />
</div>
