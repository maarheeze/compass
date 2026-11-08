<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use function array_map;

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
        $registeredAlertTypes = $this->alertTypes->alertTypes();

        return array_map(
            static function (AlertTypeGroup $alertTypeGroup) use ($registeredAlertTypes): GroupedAlertTypes {
                return new GroupedAlertTypes($alertTypeGroup, $registeredAlertTypes->ofGroup($alertTypeGroup));
            },
            $registeredAlertTypes->alertTypeGroups(),
        );
    }
}
