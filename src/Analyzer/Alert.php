<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer;

interface Alert
{
    /**
     * @return view-string
     */
    public function viewName(): string;
}
