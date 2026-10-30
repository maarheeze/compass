<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Dependabot;

enum PackageEcosystem: string
{
    case COMPOSER = 'composer';
    case NPM = 'npm';
}
