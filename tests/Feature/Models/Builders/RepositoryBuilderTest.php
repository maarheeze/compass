<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\ComposerLock;
use Maarheeze\Compass\Models\DependabotYml;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Uuid\Uuid;

test('collected keeps a collected repository', function (): void {
    $collectedRepository = Repository::factory()->create(['missing_at' => null]);

    $collectedRepositories = Repository::query()
        ->collected()
        ->get();

    expect($collectedRepositories->sole()->is($collectedRepository))
        ->toBeTrue();
});

test('collected leaves out a missing repository', function (): void {
    Repository::factory()->create(['missing_at' => fake()->dateTime()]);

    $collectedRepositories = Repository::query()
        ->collected()
        ->get();

    expect($collectedRepositories)
        ->toBeEmpty();
});

test('missing keeps a missing repository that is not ignored', function (): void {
    $missingRepository = Repository::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => null,
    ]);

    $missingRepositories = Repository::query()
        ->missing()
        ->get();

    expect($missingRepositories->sole()->is($missingRepository))
        ->toBeTrue();
});

test('missing leaves out an ignored repository', function (): void {
    Repository::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => fake()->dateTime(),
    ]);

    $missingRepositories = Repository::query()
        ->missing()
        ->get();

    expect($missingRepositories)
        ->toBeEmpty();
});

test('missing leaves out a collected repository', function (): void {
    Repository::factory()->create(['missing_at' => null]);

    $missingRepositories = Repository::query()
        ->missing()
        ->get();

    expect($missingRepositories)
        ->toBeEmpty();
});

test('not collected keeps a missing repository', function (): void {
    $notCollectedRepository = Repository::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => null,
    ]);

    $notCollectedRepositories = Repository::query()
        ->notCollected()
        ->get();

    expect($notCollectedRepositories->sole()->is($notCollectedRepository))
        ->toBeTrue();
});

test('not collected keeps an ignored repository', function (): void {
    $notCollectedRepository = Repository::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => fake()->dateTime(),
    ]);

    $notCollectedRepositories = Repository::query()
        ->notCollected()
        ->get();

    expect($notCollectedRepositories->sole()->is($notCollectedRepository))
        ->toBeTrue();
});

test('not collected leaves out a collected repository', function (): void {
    Repository::factory()->create(['missing_at' => null]);

    $notCollectedRepositories = Repository::query()
        ->notCollected()
        ->get();

    expect($notCollectedRepositories)
        ->toBeEmpty();
});

test('returned by run keeps a repository returned by the run', function (): void {
    $repository = Repository::factory()->create();

    $repositoriesReturnedByRun = Repository::query()
        ->returnedByRun($repository->run_id)
        ->get();

    expect($repositoriesReturnedByRun->sole()->is($repository))
        ->toBeTrue();
});

test('returned by run leaves out a repository returned by another run', function (): void {
    Repository::factory()->create();

    $repositoriesReturnedByRun = Repository::query()
        ->returnedByRun(Uuid::generate())
        ->get();

    expect($repositoriesReturnedByRun)
        ->toBeEmpty();
});

test('not returned by run keeps a repository returned by another run', function (): void {
    $repository = Repository::factory()->create();

    $repositoriesNotReturnedByRun = Repository::query()
        ->notReturnedByRun(Uuid::generate())
        ->get();

    expect($repositoriesNotReturnedByRun->sole()->is($repository))
        ->toBeTrue();
});

test('not returned by run leaves out a repository returned by the run', function (): void {
    $repository = Repository::factory()->create();

    $repositoriesNotReturnedByRun = Repository::query()
        ->notReturnedByRun($repository->run_id)
        ->get();

    expect($repositoriesNotReturnedByRun)
        ->toBeEmpty();
});

test('composer json out of date keeps a repository whose composer.json changed', function (): void {
    $repository = Repository::factory()->create(['composer_json_version' => fake()->unique()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'version' => fake()->unique()->sha1(),
    ]);

    $repositoriesWithComposerJsonOutOfDate = Repository::query()
        ->composerJsonOutOfDate()
        ->get();

    expect($repositoriesWithComposerJsonOutOfDate->sole()->is($repository))
        ->toBeTrue();
});

test('composer json out of date keeps a repository whose composer.json was removed', function (): void {
    $repository = Repository::factory()->create(['composer_json_version' => null]);
    ComposerJson::factory()->create(['repository_id' => $repository->id]);

    $repositoriesWithComposerJsonOutOfDate = Repository::query()
        ->composerJsonOutOfDate()
        ->get();

    expect($repositoriesWithComposerJsonOutOfDate->sole()->is($repository))
        ->toBeTrue();
});

test('composer json out of date leaves out a repository whose composer.json is unchanged', function (): void {
    $repository = Repository::factory()->create(['composer_json_version' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'version' => $repository->composer_json_version,
    ]);

    $repositoriesWithComposerJsonOutOfDate = Repository::query()
        ->composerJsonOutOfDate()
        ->get();

    expect($repositoriesWithComposerJsonOutOfDate)
        ->toBeEmpty();
});

test('composer json out of date leaves out a repository that never had a composer.json', function (): void {
    Repository::factory()->create(['composer_json_version' => null]);

    $repositoriesWithComposerJsonOutOfDate = Repository::query()
        ->composerJsonOutOfDate()
        ->get();

    expect($repositoriesWithComposerJsonOutOfDate)
        ->toBeEmpty();
});

test('composer lock out of date keeps a repository whose composer.lock changed', function (): void {
    $repository = Repository::factory()->create(['composer_lock_version' => fake()->unique()->sha1()]);
    ComposerLock::factory()->create([
        'repository_id' => $repository->id,
        'version' => fake()->unique()->sha1(),
    ]);

    $repositoriesWithComposerLockOutOfDate = Repository::query()
        ->composerLockOutOfDate()
        ->get();

    expect($repositoriesWithComposerLockOutOfDate->sole()->is($repository))
        ->toBeTrue();
});

test('composer lock out of date keeps a repository whose composer.lock was removed', function (): void {
    $repository = Repository::factory()->create(['composer_lock_version' => null]);
    ComposerLock::factory()->create(['repository_id' => $repository->id]);

    $repositoriesWithComposerLockOutOfDate = Repository::query()
        ->composerLockOutOfDate()
        ->get();

    expect($repositoriesWithComposerLockOutOfDate->sole()->is($repository))
        ->toBeTrue();
});

test('composer lock out of date leaves out a repository whose composer.lock is unchanged', function (): void {
    $repository = Repository::factory()->create(['composer_lock_version' => fake()->sha1()]);
    ComposerLock::factory()->create([
        'repository_id' => $repository->id,
        'version' => $repository->composer_lock_version,
    ]);

    $repositoriesWithComposerLockOutOfDate = Repository::query()
        ->composerLockOutOfDate()
        ->get();

    expect($repositoriesWithComposerLockOutOfDate)
        ->toBeEmpty();
});

test('composer lock out of date leaves out a repository that never had a composer.lock', function (): void {
    Repository::factory()->create(['composer_lock_version' => null]);

    $repositoriesWithComposerLockOutOfDate = Repository::query()
        ->composerLockOutOfDate()
        ->get();

    expect($repositoriesWithComposerLockOutOfDate)
        ->toBeEmpty();
});

test('dependabot yml out of date keeps a repository whose dependabot.yml changed', function (): void {
    $repository = Repository::factory()->create(['dependabot_yml_version' => fake()->unique()->sha1()]);
    DependabotYml::factory()->create([
        'repository_id' => $repository->id,
        'version' => fake()->unique()->sha1(),
    ]);

    $repositoriesWithDependabotYmlOutOfDate = Repository::query()
        ->dependabotYmlOutOfDate()
        ->get();

    expect($repositoriesWithDependabotYmlOutOfDate->sole()->is($repository))
        ->toBeTrue();
});

test('dependabot yml out of date keeps a repository whose dependabot.yml was removed', function (): void {
    $repository = Repository::factory()->create(['dependabot_yml_version' => null]);
    DependabotYml::factory()->create(['repository_id' => $repository->id]);

    $repositoriesWithDependabotYmlOutOfDate = Repository::query()
        ->dependabotYmlOutOfDate()
        ->get();

    expect($repositoriesWithDependabotYmlOutOfDate->sole()->is($repository))
        ->toBeTrue();
});

test('dependabot yml out of date leaves out a repository whose dependabot.yml is unchanged', function (): void {
    $repository = Repository::factory()->create(['dependabot_yml_version' => fake()->sha1()]);
    DependabotYml::factory()->create([
        'repository_id' => $repository->id,
        'version' => $repository->dependabot_yml_version,
    ]);

    $repositoriesWithDependabotYmlOutOfDate = Repository::query()
        ->dependabotYmlOutOfDate()
        ->get();

    expect($repositoriesWithDependabotYmlOutOfDate)
        ->toBeEmpty();
});

test('dependabot yml out of date leaves out a repository that never had a dependabot.yml', function (): void {
    Repository::factory()->create(['dependabot_yml_version' => null]);

    $repositoriesWithDependabotYmlOutOfDate = Repository::query()
        ->dependabotYmlOutOfDate()
        ->get();

    expect($repositoriesWithDependabotYmlOutOfDate)
        ->toBeEmpty();
});

test('of external ids keeps a repository with one of the ids', function (): void {
    $repository = Repository::factory()->create();

    $repositoriesOfExternalIds = Repository::query()
        ->ofExternalIds(new Collection([$repository->external_id]))
        ->get();

    expect($repositoriesOfExternalIds->sole()->is($repository))
        ->toBeTrue();
});

test('of external ids leaves out a repository with another id', function (): void {
    Repository::factory()->create();

    $repositoriesOfExternalIds = Repository::query()
        ->ofExternalIds(new Collection([fake()->unique()->sha1()]))
        ->get();

    expect($repositoriesOfExternalIds)
        ->toBeEmpty();
});

test('of full names keeps a repository with one of the full names', function (): void {
    $repository = Repository::factory()->create();

    $repositoriesOfFullNames = Repository::query()
        ->ofFullNames(new Collection([$repository->full_name]))
        ->get();

    expect($repositoriesOfFullNames->sole()->is($repository))
        ->toBeTrue();
});

test('of full names leaves out a repository with another full name', function (): void {
    Repository::factory()->create();

    $repositoriesOfFullNames = Repository::query()
        ->ofFullNames(new Collection([sprintf('%s/%s', fake()->slug(), fake()->unique()->slug())]))
        ->get();

    expect($repositoriesOfFullNames)
        ->toBeEmpty();
});

test('of source keeps a repository of the source', function (): void {
    $repositoryOfSource = Repository::factory()->create();

    $repositoriesOfSource = Repository::query()
        ->ofSource($repositoryOfSource->source)
        ->get();

    expect($repositoriesOfSource->sole()->is($repositoryOfSource))
        ->toBeTrue();
});

test('of source leaves out a repository of another source', function (): void {
    Repository::factory()->create(['source' => fake()->unique()->slug()]);

    $repositoriesOfSource = Repository::query()
        ->ofSource(fake()->unique()->slug())
        ->get();

    expect($repositoriesOfSource)
        ->toBeEmpty();
});
