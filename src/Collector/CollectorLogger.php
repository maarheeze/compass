<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Illuminate\Log\LogManager;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Stringable;

readonly class CollectorLogger implements LoggerInterface
{
    use LoggerTrait;

    public function __construct(
        private ?string $channel,
        private LogManager $logManager,
    ) {
    }

    /**
     * @param array<mixed> $context
     */
    public function log(mixed $level, string|Stringable $message, array $context = []): void
    {
        $this->logManager
            ->channel($this->channel)
            ->log($level, $message, $context);
    }
}
