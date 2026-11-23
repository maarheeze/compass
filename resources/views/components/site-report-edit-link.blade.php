<a
    href="{{ $url }}"
    aria-label="{{ __('compass::site-report.edit') }}"
    title="{{ __('compass::site-report.edit') }}"
    {{ $attributes->merge(['class' => 'text-foreground-muted hover:text-accent']) }}>
    <x-compass::icons.gear />
</a>
