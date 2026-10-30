<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\View\BadgeType;
use Maarheeze\Compass\View\Components\MissingLabel;

test('badge type is a warning for a missing subject', function (): void {
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);

    $missingLabel = $this->app->make(MissingLabel::class, ['subject' => $site]);

    expect($missingLabel->badgeType())
        ->toBe(BadgeType::WARNING);
});

test('badge type is neutral for an ignored subject', function (): void {
    $repository = Repository::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => fake()->dateTime(),
    ]);

    $missingLabel = $this->app->make(MissingLabel::class, ['subject' => $repository]);

    expect($missingLabel->badgeType())
        ->toBe(BadgeType::NEUTRAL);
});

test('badge type is not given for a collected subject', function (): void {
    $repository = Repository::factory()->create(['missing_at' => null]);

    $missingLabel = $this->app->make(MissingLabel::class, ['subject' => $repository]);

    $this->expectException(LogicException::class);

    $missingLabel->badgeType();
});

test('should render when the subject is missing', function (): void {
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);

    $missingLabel = $this->app->make(MissingLabel::class, ['subject' => $site]);

    expect($missingLabel->shouldRender())
        ->toBeTrue();
});

test('should render when the subject is ignored', function (): void {
    $site = Site::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => fake()->dateTime(),
    ]);

    $missingLabel = $this->app->make(MissingLabel::class, ['subject' => $site]);

    expect($missingLabel->shouldRender())
        ->toBeTrue();
});

test('should not render when the subject is collected', function (): void {
    $repository = Repository::factory()->create(['missing_at' => null]);

    $missingLabel = $this->app->make(MissingLabel::class, ['subject' => $repository]);

    expect($missingLabel->shouldRender())
        ->toBeFalse();
});

test('render renders the missing label view', function (): void {
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);

    $missingLabel = $this->app->make(MissingLabel::class, ['subject' => $site]);

    expect($missingLabel->render()->name())
        ->toBe('compass::components.missing-label');
});
