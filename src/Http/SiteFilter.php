<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Http;

use Illuminate\Contracts\Translation\Translator;
use Maarheeze\Compass\Models\Builders\SiteBuilder;
use Maarheeze\Compass\View\Filter;
use Webmozart\Assert\Assert;

readonly class SiteFilter
{
    public const string PHP_VERSION_PARAMETER = 'php';
    public const string SERVER_PARAMETER = 'server';

    public function __construct(
        public ?string $serverName,
        public ?string $phpVersion,
    ) {
    }

    public function phpVersionFilter(SiteBuilder $sites, Translator $translator): Filter
    {
        return new Filter(
            Assert::string($translator->get('compass::site.php_version')),
            [self::PHP_VERSION_PARAMETER],
            $this
                ->sitesOfServer($sites)
                ->listed()
                ->phpVersionFilterOptions(),
        );
    }

    public function serverFilter(SiteBuilder $sites, Translator $translator): Filter
    {
        return new Filter(
            Assert::string($translator->get('compass::site.server')),
            [self::SERVER_PARAMETER],
            $this
                ->sitesOfPhpVersion($sites)
                ->listed()
                ->serverFilterOptions(),
        );
    }

    public function sites(SiteBuilder $sites): SiteBuilder
    {
        return $this->sitesOfPhpVersion($this->sitesOfServer($sites));
    }

    private function sitesOfPhpVersion(SiteBuilder $sites): SiteBuilder
    {
        if ($this->phpVersion === null) {
            return $sites;
        }

        return $sites->ofPhpVersion($this->phpVersion);
    }

    private function sitesOfServer(SiteBuilder $sites): SiteBuilder
    {
        if ($this->serverName === null) {
            return $sites;
        }

        return $sites->ofServer($this->serverName);
    }
}
