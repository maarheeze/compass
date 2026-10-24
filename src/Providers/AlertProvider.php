<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Providers;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Maarheeze\Compass\Analyzer\AlertFinder;
use Maarheeze\Compass\Analyzer\Client\DebugEnabledAlertDetector;
use Maarheeze\Compass\Analyzer\Client\IncompatibleClientAlertDetector;
use Maarheeze\Compass\Analyzer\Client\NonProductionAlertDetector;
use Maarheeze\Compass\Analyzer\Client\UnauthorizedClientAlertDetector;
use Maarheeze\Compass\Analyzer\Composer\ComposerWarningAlertDetector;
use Maarheeze\Compass\Analyzer\Forge\MissingSiteAlertDetector;
use Maarheeze\Compass\Analyzer\Forge\SiteBehindAlertDetector;
use Maarheeze\Compass\Analyzer\GitHub\MissingRepositoryAlertDetector;
use Maarheeze\Compass\Analyzer\SiteEndpoint\DownSiteEndpointAlertDetector;
use Maarheeze\Compass\Collections\AlertDetectorCollection;

class AlertProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app
            ->when(AlertFinder::class)
            ->needs(AlertDetectorCollection::class)
            ->give(static function (Container $container): AlertDetectorCollection {
                return new AlertDetectorCollection([
                    $container->make(MissingRepositoryAlertDetector::class),
                    $container->make(MissingSiteAlertDetector::class),
                    $container->make(ComposerWarningAlertDetector::class),
                    $container->make(SiteBehindAlertDetector::class),
                    $container->make(DebugEnabledAlertDetector::class),
                    $container->make(NonProductionAlertDetector::class),
                    $container->make(UnauthorizedClientAlertDetector::class),
                    $container->make(IncompatibleClientAlertDetector::class),
                    $container->make(DownSiteEndpointAlertDetector::class),
                ]);
            });
    }
}
