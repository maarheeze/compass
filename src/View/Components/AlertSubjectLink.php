<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Alerts\AlertSubject;
use Maarheeze\Compass\Http\AlertSubjectUrl;

class AlertSubjectLink extends Component
{
    public readonly string $name;
    public readonly string $url;

    public function __construct(
        AlertSubject $subject,
        AlertSubjectUrl $alertSubjectUrl,
        private readonly ViewFactory $view,
    ) {
        $this->name = $subject->displayName();
        $this->url = $alertSubjectUrl->url($subject);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.alert-subject-link');
    }
}
