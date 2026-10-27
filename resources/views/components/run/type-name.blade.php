@props(['type'])

@switch ($type)
    @case (\Maarheeze\Compass\Collector\RunType::ENDPOINT_CHECK)
        Endpoint check
        @break
    @case (\Maarheeze\Compass\Collector\RunType::FORGE_SYNC)
        Forge sync
        @break
    @case (\Maarheeze\Compass\Collector\RunType::GITHUB_SYNC)
        GitHub sync
        @break
@endswitch
