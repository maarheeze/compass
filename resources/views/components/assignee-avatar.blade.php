@props(['assignee'])

@if ($assignee->user)
    <img
        src="{{ $assignee->user->avatar_url }}"
        alt="{{ $assignee->login }}"
        title="{{ $assignee->login }}"
        {{ $attributes->merge(['class' => 'max-w-none rounded-full border border-border']) }}>
@else
    <span
        title="{{ $assignee->login }}"
        aria-label="{{ $assignee->login }}"
        {{ $attributes->merge(['class' => 'flex items-center justify-center rounded-full border border-border bg-canvas-subtle text-foreground-muted']) }}>
        <x-compass::icons.person class="size-3" />
    </span>
@endif
