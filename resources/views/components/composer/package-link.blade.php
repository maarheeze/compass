@props(['packageName'])

<a href="{{ route(\Maarheeze\Compass\Http\RouteName::GITHUB_PACKAGE, $packageName) }}" class="text-accent hover:underline">{{ $packageName }}</a>
