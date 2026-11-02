<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

interface SourceDriver
{
    /**
     * @param array<string, mixed> $config
     */
    public static function settings(array $config): object;
}
