<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\View\LabelledEnum;
use Webmozart\Assert\Assert;

class EnumLabel extends Component
{
    public readonly string $label;

    public function __construct(
        LabelledEnum $value,
        Translator $translator,
        private readonly ViewFactory $view,
    ) {
        $this->label = Assert::string($translator->get($value->labelKey()));
    }

    public function render(): View
    {
        return $this->view->make('compass::components.enum-label');
    }
}
