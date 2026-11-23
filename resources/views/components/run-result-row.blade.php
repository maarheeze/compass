@props(['runResult'])

<li class="flex flex-wrap items-center gap-x-2 gap-y-1">
    <x-compass::alert-subject-link :alert-subject="$runResult->subject" />
    <x-compass::run-result-status-badge :status="$runResult->status" />
    @if ($runResult->messages !== [])
        <span class="text-red-700 dark:text-red-300">
            @foreach ($runResult->messages as $message)
                @unless ($loop->first)
                    &middot;
                @endunless
                {{ $message }}
            @endforeach
        </span>
    @endif
</li>
