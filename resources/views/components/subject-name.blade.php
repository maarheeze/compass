@props(['subject'])

<div class="flex min-w-0 items-center gap-2">
    {{ $slot }}
    <x-compass::missing-label :missing-at="$subject->missing_at" :missing-ignored-at="$subject->missing_ignored_at" />
    <x-compass::alert-icon :subject="$subject" />
</div>
