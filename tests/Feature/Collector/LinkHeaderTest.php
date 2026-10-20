<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response as PsrResponse;
use Illuminate\Http\Client\Response;
use Maarheeze\Compass\Collector\LinkHeader;

test('next page url is the url linked as next', function (): void {
    $nextPageUrl = fake()->url();
    $response = new Response(new PsrResponse(200, [
        'Link' => sprintf('<%s>; rel="prev", <%s>; rel="next", <%s>; rel="last"', fake()->url(), $nextPageUrl, fake()->url()),
    ]));

    $linkHeader = new LinkHeader($response);

    expect($linkHeader->nextPageUrl())
        ->toBe($nextPageUrl);
});

test('next page url is null when no url is linked as next', function (): void {
    $response = new Response(new PsrResponse(200, [
        'Link' => sprintf('<%s>; rel="prev", <%s>; rel="first"', fake()->url(), fake()->url()),
    ]));

    $linkHeader = new LinkHeader($response);

    expect($linkHeader->nextPageUrl())
        ->toBeNull();
});

test('next page url is null without a link header', function (): void {
    $linkHeader = new LinkHeader(new Response(new PsrResponse()));

    expect($linkHeader->nextPageUrl())
        ->toBeNull();
});
