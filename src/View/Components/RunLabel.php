<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Collector\TaskType;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Compass\View\LabelledEnum;
use Webmozart\Assert\Assert;

use function sprintf;

class RunLabel extends Component
{
    public readonly string $label;

    public function __construct(
        Run $run,
        private readonly Translator $translator,
        private readonly ViewFactory $view,
    ) {
        $this->label = $this->runLabel($run);
    }

    public function render(): View
    {
        return $this->view->make('compass::components.run-label');
    }

    private function runLabel(Run $run): string
    {
        if ($run->collector === null) {
            return $this->translatedLabel(Assert::isInstanceOf($run->task, TaskType::class));
        }

        return sprintf('%s · %s', $run->source, $this->translatedLabel($run->collector));
    }

    private function translatedLabel(LabelledEnum $labelledEnum): string
    {
        return Assert::string($this->translator->get($labelledEnum->labelKey()));
    }
}
