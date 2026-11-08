<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View\Components;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Maarheeze\Compass\Collector\TaskType;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Compass\View\EnumLabels;
use UnitEnum;
use Webmozart\Assert\Assert;

use function sprintf;

class RunRow extends Component
{
    public function __construct(
        public readonly Run $run,
        private readonly EnumLabels $enumLabels,
        private readonly Translator $translator,
        private readonly ViewFactory $view,
    ) {
    }

    public function label(): string
    {
        if ($this->run->collector === null) {
            return $this->translatedLabel(Assert::isInstanceOf($this->run->task, TaskType::class));
        }

        return sprintf('%s · %s', $this->run->source, $this->translatedLabel($this->run->collector));
    }

    public function render(): View
    {
        return $this->view->make('compass::components.run-row');
    }

    private function translatedLabel(UnitEnum $value): string
    {
        return Assert::string($this->translator->get($this->enumLabels->labelKey($value)));
    }
}
