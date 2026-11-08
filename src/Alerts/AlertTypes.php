<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use InvalidArgumentException;
use LogicException;
use Maarheeze\Compass\Collections\AlertTypeCollection;

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

    public function alertTypes(): AlertTypeCollection
    {
        return new AlertTypeCollection(array_values($this->alertTypes));
    }

    /**
     * @return list<string>
     */
    public function identifiers(): array
    {
        return array_keys($this->alertTypes);
    }

    public function isRegistered(AlertType $alertType): bool
    {
        return array_key_exists($alertType->identifier(), $this->alertTypes);
    }

    public function register(AlertType $alertType): void
    {
        if ($this->isRegistered($alertType)) {
            throw new LogicException('alert type already registered');
        }

        $this->alertTypes[$alertType->identifier()] = $alertType;
    }
}
