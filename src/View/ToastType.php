<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View;

enum ToastType: string
{
    case DANGER = 'danger';
    case NEUTRAL = 'neutral';
    case SUCCESS = 'success';
}
