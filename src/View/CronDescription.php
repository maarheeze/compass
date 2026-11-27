<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View;

use Illuminate\Contracts\Translation\Translator;
use Lorisleiva\CronTranslator\CronParsingException;
use Lorisleiva\CronTranslator\CronTranslator;
use Webmozart\Assert\Assert;

use function preg_replace;

readonly class CronDescription
{
    private const string SINGLE_DIGIT_HOUR_PATTERN = '/\b(\d):(\d{2})\b/';

    public function __construct(
        private Translator $translator,
    ) {
    }

    public function describe(string $expression): string
    {
        try {
            $description = CronTranslator::translate($expression, $this->translator->getLocale(), true);
        } catch (CronParsingException) {
            return $expression;
        }

        return Assert::string(preg_replace(self::SINGLE_DIGIT_HOUR_PATTERN, '0$1:$2', $description));
    }
}
