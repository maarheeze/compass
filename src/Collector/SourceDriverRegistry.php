<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Webmozart\Assert\Assert;

class SourceDriverRegistry
{
    /** @var array<string, class-string<SourceDriver>> */
    private array $driverClasses = [];

    /**
     * @return class-string<SourceDriver>
     */
    public function driverClass(string $name): string
    {
        Assert::keyExists($this->driverClasses, $name);

        return $this->driverClasses[$name];
    }

    /**
     * @param class-string<SourceDriver> $driverClass
     */
    public function register(string $name, string $driverClass): void
    {
        Assert::implementsInterface($driverClass, SourceDriver::class);

        $this->driverClasses[$name] = $driverClass;
    }
}
