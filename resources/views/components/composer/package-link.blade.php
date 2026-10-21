@props(['packageName'])

<a href="{{ route(\Maarheeze\Compass\Http\RouteName::GITHUB_PACKAGE, $packageName) }}" class="hover:underline">{{ $packageName }}</a>
