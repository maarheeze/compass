<?php

declare(strict_types=1);

use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Cache\Repository;
use Maarheeze\Compass\Collector\JobLock;
use Maarheeze\Compass\Collector\Sites\Jobs\CollectSites;
use Maarheeze\Compass\Tests\Fixtures\RecordingLockStore;
use Maarheeze\Uuid\Uuid;

test('handle runs the job while no other job holds its lock', function (): void {
    $collectSites = new CollectSites(Uuid::generate(), fake()->slug());
    $ranCollectSites = null;

    $jobLock = $this->app->make(JobLock::class);
    $jobLock->handle($collectSites, static function (CollectSites $lockedCollectSites) use (&$ranCollectSites): void {
        $ranCollectSites = $lockedCollectSites;
    });

    expect($ranCollectSites)
        ->toBe($collectSites);
});

test('handle releases the job after the configured seconds while another job holds its lock', function (): void {
    $lockRetrySeconds = fake()->numberBetween(1, 600);
    $this->setConfiguration('compass.lock_retry_seconds', $lockRetrySeconds);
    $collectSites = new CollectSites(Uuid::generate(), fake()->slug())->withFakeQueueInteractions();

    $jobLock = $this->app->make(JobLock::class);
    $jobLock->handle($collectSites, static function (CollectSites $lockedCollectSites) use ($jobLock): void {
        $jobLock->handle($lockedCollectSites, static function (): void {
        });
    });

    $collectSites->assertReleased($lockRetrySeconds);
});

test('handle runs a job with another lock key while a job holds its lock', function (): void {
    $collectSites = new CollectSites(Uuid::generate(), fake()->unique()->slug());
    $otherCollectSites = new CollectSites(Uuid::generate(), fake()->unique()->slug());
    $ranOtherCollectSites = null;

    $jobLock = $this->app->make(JobLock::class);
    $jobLock->handle(
        $collectSites,
        static function () use ($jobLock, $otherCollectSites, &$ranOtherCollectSites): void {
            $jobLock->handle(
                $otherCollectSites,
                static function (CollectSites $lockedCollectSites) use (&$ranOtherCollectSites): void {
                    $ranOtherCollectSites = $lockedCollectSites;
                },
            );
        },
    );

    expect($ranOtherCollectSites)
        ->toBe($otherCollectSites);
});

test('handle holds the lock for the configured minutes', function (): void {
    $lockExpiryMinutes = fake()->numberBetween(1, 60);
    $this->setConfiguration('compass.lock_expiry_minutes', $lockExpiryMinutes);
    $recordingLockStore = new RecordingLockStore();
    $this->app->instance(Repository::class, new CacheRepository($recordingLockStore));
    $collectSites = new CollectSites(Uuid::generate(), fake()->slug());

    $jobLock = $this->app->make(JobLock::class);
    $jobLock->handle($collectSites, static function (): void {
    });

    expect($recordingLockStore->lockedSeconds)
        ->toBe([$lockExpiryMinutes * 60]);
});
