<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Alerts\AlertTypes;
use Webmozart\Assert\Assert;

class AlertTypeLabel extends Component
{
    public readonly string $label;

    public function __construct(
        string $identifier,
        AlertTypes $alertTypes,
        Translator $translator,
        private readonly ViewFactory $view,
    ) {
        $alertType = $alertTypes->alertType($identifier);
        $this->label = Assert::string($translator->get($alertType->translationKey()));
    }

    public function render(): View
    {
        return $this->view->make('compass::components.alert-type-label');
    }
}
