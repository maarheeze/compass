<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\View\Components\SiteMissingNotice;

test('delete url is the delete action of the site', function (): void {
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);

    $siteMissingNotice = $this->app->make(SiteMissingNotice::class, ['site' => $site]);

    expect($siteMissingNotice->deleteUrl)
        ->toBe(route(RouteName::FORGE_SITE_DELETE, $site));
});

test('ignore url is the ignore action of the site', function (): void {
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);

    $siteMissingNotice = $this->app->make(SiteMissingNotice::class, ['site' => $site]);

    expect($siteMissingNotice->ignoreUrl)
        ->toBe(route(RouteName::FORGE_SITE_IGNORE, $site));
});

test('should render when the site is missing', function (): void {
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);

    $siteMissingNotice = $this->app->make(SiteMissingNotice::class, ['site' => $site]);

    expect($siteMissingNotice->shouldRender())
        ->toBeTrue();
});

test('should not render when the site is not missing', function (): void {
    $site = Site::factory()->create(['missing_at' => null]);

    $siteMissingNotice = $this->app->make(SiteMissingNotice::class, ['site' => $site]);

    expect($siteMissingNotice->shouldRender())
        ->toBeFalse();
});

test('render renders the missing notice view', function (): void {
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);

    $siteMissingNotice = $this->app->make(SiteMissingNotice::class, ['site' => $site]);

    expect($siteMissingNotice->render()->name())
        ->toBe('compass::components.missing-notice');
});
