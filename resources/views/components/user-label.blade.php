<span class="inline-flex items-center gap-2">
    @if ($user->avatar_url)
        <img src="{{ $user->avatar_url }}" alt="" class="size-5 rounded-full border border-border">
    @endif
    {{ $user->nickname }}
</span>
