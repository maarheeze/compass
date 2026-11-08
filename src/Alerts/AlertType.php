<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

interface AlertType
{
    public function group(): AlertTypeGroup;

    public function identifier(): string;

    public function translationKey(): string;
}
