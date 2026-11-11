<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View;

use UnitEnum;

interface LabelledEnum extends UnitEnum
{
    public function labelKey(): string;
}
