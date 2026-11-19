<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collections;

use Illuminate\Database\Eloquent\Collection;
use Maarheeze\Compass\Http\SiteFilter;
use Maarheeze\Compass\Models\Site;

/**
 * @extends Collection<array-key, Site>
 */
class SiteCollection extends Collection
{
    public function ofPhpVersion(string $phpVersion): self
    {
        return $this->filter(static function (Site $site) use ($phpVersion): bool {
            return $site->php_version === $phpVersion;
        });
    }

    public function ofServer(string $serverName): self
    {
        return $this->filter(static function (Site $site) use ($serverName): bool {
            return $site->server_name === $serverName;
        });
    }

    public function phpVersionFilterOptions(): FilterOptionCollection
    {
        $phpVersionFilterOptions = new FilterOptionCollection();

        foreach ($this as $site) {
            if ($site->php_version === null) {
                continue;
            }

            $phpVersionFilterOptions->countOption(
                $site->php_version,
                [SiteFilter::PHP_VERSION_PARAMETER => $site->php_version],
                null,
            );
        }

        return $phpVersionFilterOptions->sortedByLabelDescending();
    }

    public function serverFilterOptions(): FilterOptionCollection
    {
        $serverFilterOptions = new FilterOptionCollection();

        foreach ($this as $site) {
            $serverFilterOptions->countOption(
                $site->server_name,
                [SiteFilter::SERVER_PARAMETER => $site->server_name],
                null,
            );
        }

        return $serverFilterOptions->sortedByLabel();
    }
}
