<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\View\EnumLabels;
use UnitEnum;

class EnumLabel extends Component
{
    public readonly string $labelKey;

    public function __construct(
        UnitEnum $value,
        EnumLabels $enumLabels,
        private readonly ViewFactory $view,
    ) {
        $this->labelKey = $enumLabels->labelKey($value);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.enum-label');
    }
}
