@if ($commitHash !== null)
    @if ($url !== null)
        <a
            href="{{ $url }}"
            target="_blank"
            rel="noopener"
            title="{{ $commitHash }}"
            {{ $attributes->merge(['class' => 'block w-[7ch] overflow-hidden font-mono text-accent hover:underline']) }}><code>{{ $commitHash }}</code></a>
    @else
        <code title="{{ $commitHash }}" {{ $attributes->merge(['class' => 'block w-[7ch] overflow-hidden']) }}>{{ $commitHash }}</code>
    @endif
@endif
