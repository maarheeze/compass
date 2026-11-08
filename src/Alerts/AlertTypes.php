<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use InvalidArgumentException;

use function array_key_exists;
use function array_keys;
use function array_values;

class AlertTypes
{
    /** @var array<string, AlertType> */
    private array $alertTypes = [];

    public function alertType(string $identifier): AlertType
    {
        if (!array_key_exists($identifier, $this->alertTypes)) {
            throw new InvalidArgumentException('unknown alert type');
        }

        return $this->alertTypes[$identifier];
    }

    /**
     * @return list<AlertType>
     */
    public function alertTypes(): array
    {
        return array_values($this->alertTypes);
    }

    /**
     * @return list<string>
     */
    public function identifiers(): array
    {
        return array_keys($this->alertTypes);
    }

    public function register(AlertType $alertType): void
    {
        $this->alertTypes[$alertType->identifier()] = $alertType;
    }
}
