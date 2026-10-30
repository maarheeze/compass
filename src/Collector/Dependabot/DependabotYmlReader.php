<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Dependabot;

use Illuminate\Support\Collection;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;
use Webmozart\Assert\Assert;

use function array_key_exists;

readonly class DependabotYmlReader
{
    public function __construct(
        private Parser $parser,
    ) {
    }

    /**
     * @return Collection<int, string>
     *
     * @throws ParseException
     */
    public function packageEcosystems(string $yaml): Collection
    {
        $dependabotYml = Assert::isArray($this->parser->parse($yaml));
        $packageEcosystems = new Collection();

        if (!array_key_exists('updates', $dependabotYml)) {
            return $packageEcosystems;
        }

        foreach (Assert::isList($dependabotYml['updates']) as $update) {
            $packageEcosystems->push(Assert::string(Assert::isArray($update)['package-ecosystem']));
        }

        return $packageEcosystems->unique()->values();
    }
}
