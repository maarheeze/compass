<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\GitHub\BlobQuery;

test('text is queried for the blob of the repository in the organization', function (): void {
    $organization = fake()->slug();
    $this->setConfiguration('compass.github.organization', $organization);
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

    $blobQuery = $this->app->make(BlobQuery::class);
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

    $blobQuery = $this->app->make(BlobQuery::class);

    expect($blobQuery->text(fake()->slug(), fake()->sha1()))
        ->toBe($text);
});
