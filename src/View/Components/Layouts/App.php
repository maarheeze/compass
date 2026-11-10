<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components\Layouts;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

use function str_replace;

class App extends Component
{
    public readonly string $htmlLanguage;

    public function __construct(
        Translator $translator,
        private readonly ViewFactory $view,
    ) {
        $this->htmlLanguage = str_replace('_', '-', $translator->getLocale());
    }

    public function render(): View
    {
        return $this->view->make('compass::components.layouts.app');
    }
}
