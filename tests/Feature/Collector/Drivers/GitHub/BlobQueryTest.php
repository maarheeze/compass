<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\Drivers\GitHub\BlobQuery;
use Maarheeze\Compass\Collector\Drivers\GitHub\GitHubClient;

test('text is requested for the blob of the repository in the organisation', function (): void {
    $organisation = fake()->slug();
    $repositoryName = fake()->slug();
    $oid = fake()->sha1();

    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'content' => base64_encode(fake()->text()),
            'encoding' => 'base64',
        ]),
    ]);

    $blobQuery = new BlobQuery(new GitHubClient($http, fake()->sha1()), $organisation);
    $blobQuery->text($repositoryName, $oid);

    $http->assertSent(static function (HttpRequest $request) use ($organisation, $repositoryName, $oid): bool {
        return $request->url() === sprintf(
            'https://api.github.com/repos/%s/%s/git/blobs/%s',
            $organisation,
            $repositoryName,
            $oid,
        );
    });
});

test('text is the decoded content of the blob in the response', function (): void {
    $text = fake()->text();
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'content' => chunk_split(base64_encode($text), 60, "\n"),
            'encoding' => 'base64',
        ]),
    ]);

    $blobQuery = new BlobQuery(new GitHubClient($this->app->make(HttpFactory::class), fake()->sha1()), fake()->slug());

    expect($blobQuery->text(fake()->slug(), fake()->sha1()))
        ->toBe($text);
});
