<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;
use Maarheeze\Compass\Alerts\AlertSubject;
use Webmozart\Assert\Assert;

use function sprintf;

class AlertSubjectName extends Component
{
    public function __construct(
        private readonly AlertSubject $subject,
        private readonly ViewFactory $view,
    ) {
    }

    public function render(): View
    {
        $subjectModel = Assert::isInstanceOf($this->subject, Model::class);

        return $this->view->make('compass::components.alert-subject-name', [
            'subject' => $this->subject,
            'subjectView' => sprintf('compass::alert-subjects.%s', $subjectModel->getMorphClass()),
        ]);
    }
}
