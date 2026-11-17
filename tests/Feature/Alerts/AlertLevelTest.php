<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertLevel;

test('order puts critical before warning before notice', function (): void {
    $orders = [AlertLevel::CRITICAL->order(), AlertLevel::WARNING->order(), AlertLevel::NOTICE->order()];

    $sortedOrders = $orders;
    sort($sortedOrders);

    expect($orders)
        ->toBe($sortedOrders);
});
