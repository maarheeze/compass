@props(['type'])

@switch ($type)
    @case (\Maarheeze\Compass\Collector\RunType::ENDPOINT_CHECK)
        {{ __('compass::run-type.ENDPOINT_CHECK') }}
        @break
    @case (\Maarheeze\Compass\Collector\RunType::FORGE_SYNC)
        {{ __('compass::run-type.FORGE_SYNC') }}
        @break
    @case (\Maarheeze\Compass\Collector\RunType::GITHUB_SYNC)
        {{ __('compass::run-type.GITHUB_SYNC') }}
        @break
@endswitch
