<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\Dependabot\DependabotYmlReader;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

test('package ecosystems are the package ecosystems of the updates', function (): void {
    $packageEcosystems = fake()->randomElements(['composer', 'docker', 'github-actions', 'npm'], 2);

    $dependabotYmlReader = $this->app->make(DependabotYmlReader::class);
    $readPackageEcosystems = $dependabotYmlReader->packageEcosystems(Yaml::dump([
        'version' => 2,
        'updates' => [
            [
                'package-ecosystem' => $packageEcosystems[0],
                'directory' => '/',
            ],
            [
                'package-ecosystem' => $packageEcosystems[1],
                'directory' => '/',
            ],
        ],
    ]));

    expect($readPackageEcosystems->all())
        ->toBe($packageEcosystems);
});

test('package ecosystems hold a package ecosystem updated in several directories once', function (): void {
    $packageEcosystem = fake()->randomElement(['composer', 'docker', 'github-actions', 'npm']);

    $dependabotYmlReader = $this->app->make(DependabotYmlReader::class);
    $readPackageEcosystems = $dependabotYmlReader->packageEcosystems(Yaml::dump([
        'version' => 2,
        'updates' => [
            [
                'package-ecosystem' => $packageEcosystem,
                'directory' => '/',
            ],
            [
                'package-ecosystem' => $packageEcosystem,
                'directory' => sprintf('/%s', fake()->slug()),
            ],
        ],
    ]));

    expect($readPackageEcosystems->all())
        ->toBe([$packageEcosystem]);
});

test('package ecosystems are empty when the dependabot yml has no updates', function (): void {
    $dependabotYmlReader = $this->app->make(DependabotYmlReader::class);
    $readPackageEcosystems = $dependabotYmlReader->packageEcosystems(Yaml::dump(['version' => 2]));

    expect($readPackageEcosystems)
        ->toBeEmpty();
});

test('package ecosystems throw when the dependabot yml is not valid yaml', function (): void {
    $dependabotYmlReader = $this->app->make(DependabotYmlReader::class);
    $dependabotYmlReader->packageEcosystems("updates:\n  - [");
})->throws(ParseException::class);
