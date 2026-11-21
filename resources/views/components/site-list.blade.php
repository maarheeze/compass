@props(['sites', 'siteCount'])

<x-compass::list>
    <x-slot:icon>
        <x-compass::icons.server />
    </x-slot:icon>
    <x-slot:title>{{ __('compass::navigation.sites') }}</x-slot:title>
    <x-slot:empty>{{ __('compass::site.none') }}</x-slot:empty>
    <x-slot:counts>
        @if ($sites->isNotEmpty())
            <x-compass::badge type="neutral">{{ trans_choice('compass::site.site_count', $siteCount) }}</x-compass::badge>
        @endif
    </x-slot:counts>
    <x-slot:rows>
        @foreach ($sites as $site)
            <x-compass::site-row :site="$site" />
        @endforeach
    </x-slot:rows>
</x-compass::list>
