<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

enum ScheduleFrequency
{
    case DAILY;
    case EVERY_FIFTEEN_MINUTES;
    case EVERY_FIVE_MINUTES;
    case EVERY_THIRTY_MINUTES;
    case HOURLY;
    case WEEKLY;
}
