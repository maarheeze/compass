<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\GitHub\BlobQuery;
use Maarheeze\Compass\Collector\GitHub\GitHubClient;

test('text is queried for the blob of the repository in the organization', function (): void {
    $organization = fake()->slug();
    $repositoryName = fake()->slug();
    $oid = fake()->sha1();

    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'data' => [
                'repository' => [
                    'object' => [
                        'text' => fake()->text(),
                    ],
                ],
            ],
        ]),
    ]);

    $blobQuery = new BlobQuery(new GitHubClient($http, fake()->sha1()), $organization);
    $blobQuery->text($repositoryName, $oid);

    $http->assertSent(static function (HttpRequest $request) use ($organization, $repositoryName, $oid): bool {
        return $request['variables'] === [
            'organization' => $organization,
            'name' => $repositoryName,
            'oid' => $oid,
        ];
    });
});

test('text is the text of the blob in the response', function (): void {
    $text = fake()->text();
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'data' => [
                'repository' => [
                    'object' => [
                        'text' => $text,
                    ],
                ],
            ],
        ]),
    ]);

    $blobQuery = new BlobQuery(new GitHubClient($this->app->make(HttpFactory::class), fake()->sha1()), fake()->slug());

    expect($blobQuery->text(fake()->slug(), fake()->sha1()))
        ->toBe($text);
});
