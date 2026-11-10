<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\View\Component;
use Maarheeze\Compass\Alerts\AlertSubject;
use Webmozart\Assert\Assert;

use function sprintf;
use function trim;

class AlertSubjectName extends Component
{
    public function __construct(
        private readonly AlertSubject $subject,
        private readonly ViewFactory $view,
    ) {
    }

    public function render(): Htmlable
    {
        $subjectModel = Assert::isInstanceOf($this->subject, Model::class);
        $alertSubjectNameView = $this->view->make('compass::components.alert-subject-name', [
            'subject' => $this->subject,
            'subjectView' => sprintf('compass::alert-subjects.%s', $subjectModel->getMorphClass()),
        ]);

        return new HtmlString(trim($alertSubjectNameView->render()));
    }
}
