<?php

declare(strict_types=1);

use Illuminate\Config\Repository;
use Illuminate\Log\LogManager;
use Maarheeze\Compass\Collector\CollectorLogger;
use Monolog\Handler\TestHandler;
use Psr\Log\LogLevel;

test('log writes the record to the configured channel', function (): void {
    $channel = fake()->slug();
    $this->app->make(Repository::class)->set(sprintf('logging.channels.%s', $channel), [
        'driver' => 'monolog',
        'handler' => TestHandler::class,
        'level' => LogLevel::DEBUG,
    ]);
    $this->setConfiguration('compass.log_channel', $channel);
    $message = fake()->sentence();

    $collectorLogger = $this->app->make(CollectorLogger::class);
    $collectorLogger->log(LogLevel::DEBUG, $message);

    $testHandler = $this->app
        ->make(LogManager::class)
        ->channel($channel)
        ->getLogger()
        ->getHandlers()[0];

    expect($testHandler->hasDebug($message))
        ->toBeTrue();
});
