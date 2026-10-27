@props(['icon', 'title', 'assignedUrl', 'assignedCount', 'openUrl', 'openCount'])

<section class="rounded-md border border-border bg-canvas p-4">
    <x-compass::section-heading>
        <x-slot:icon>
            {{ $icon }}
        </x-slot:icon>
        {{ $title }}
    </x-compass::section-heading>

    <div class="mt-3 grid grid-cols-2 gap-4">
        <a href="{{ $assignedUrl }}" class="group">
            <p class="text-2xl font-semibold text-foreground group-hover:underline">{{ $assignedCount }}</p>
            <p class="text-xs text-foreground-muted">{{ __('compass::filter.assigned_to_me') }}</p>
        </a>
        <a href="{{ $openUrl }}" class="group">
            <p class="text-2xl font-semibold text-foreground group-hover:underline">{{ $openCount }}</p>
            <p class="text-xs text-foreground-muted">{{ __('compass::filter.open') }}</p>
        </a>
    </div>
</section>
