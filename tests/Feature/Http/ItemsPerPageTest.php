<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\ItemsPerPage;

test('items per page is the configured number of items per page', function (): void {
    $configuredItemsPerPage = fake()->numberBetween(1, 100);
    $this->setConfiguration('compass.items_per_page', $configuredItemsPerPage);

    $itemsPerPage = $this->app->make(ItemsPerPage::class);

    expect($itemsPerPage->value)
        ->toBe($configuredItemsPerPage);
});
