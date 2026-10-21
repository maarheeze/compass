<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\Forge\ServerNameFilter;
use Maarheeze\Compass\Tests\Fixtures\ForgeSiteFactory;

test('matches a site whose server name matches the pattern', function (): void {
    $forgeSite = ForgeSiteFactory::make();

    $serverNameFilter = new ServerNameFilter(sprintf('/^%s$/', preg_quote($forgeSite->serverName, '/')));
    $matches = $serverNameFilter->matches($forgeSite);

    expect($matches)
        ->toBeTrue();
});

test('does not match a site whose server name does not match the pattern', function (): void {
    $forgeSite = ForgeSiteFactory::make();

    $serverNameFilter = new ServerNameFilter(sprintf('/^%s$/', preg_quote(fake()->unique()->sha1(), '/')));
    $matches = $serverNameFilter->matches($forgeSite);

    expect($matches)
        ->toBeFalse();
});
