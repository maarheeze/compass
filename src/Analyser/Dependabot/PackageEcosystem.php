<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyser\Dependabot;

enum PackageEcosystem: string
{
    case COMPOSER = 'composer';
    case NPM = 'npm';
}
