<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use function array_filter;
use function array_map;
use function array_values;
use function in_array;

readonly class AlertTypeGroups
{
    public function __construct(
        private AlertTypes $alertTypes,
    ) {
    }

    /**
     * @return list<GroupedAlertTypes>
     */
    public function grouped(): array
    {
        return array_map(
            function (AlertTypeGroup $alertTypeGroup): GroupedAlertTypes {
                return new GroupedAlertTypes($alertTypeGroup, $this->alertTypesOfGroup($alertTypeGroup));
            },
            $this->registeredAlertTypeGroups(),
        );
    }

    /**
     * @return list<AlertType>
     */
    private function alertTypesOfGroup(AlertTypeGroup $alertTypeGroup): array
    {
        return array_values(array_filter(
            $this->alertTypes->alertTypes(),
            static function (AlertType $alertType) use ($alertTypeGroup): bool {
                return $alertType->group() === $alertTypeGroup;
            },
        ));
    }

    /**
     * @return list<AlertTypeGroup>
     */
    private function registeredAlertTypeGroups(): array
    {
        $registeredAlertTypeGroups = [];

        foreach ($this->alertTypes->alertTypes() as $alertType) {
            if (!in_array($alertType->group(), $registeredAlertTypeGroups, true)) {
                $registeredAlertTypeGroups[] = $alertType->group();
            }
        }

        return $registeredAlertTypeGroups;
    }
}
