<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Alerts\Events\ConditionPresent;
use Maarheeze\Compass\Alerts\Listeners\AlertRecorder;

class AlertProvider extends ServiceProvider
{
    public function boot(Dispatcher $events): void
    {
        $events->listen(ConditionPresent::class, AlertRecorder::class);
    }
}
