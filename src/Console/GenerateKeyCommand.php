<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Maarheeze\Compass\Collector\Client\ClientKey;
use Symfony\Component\Console\Input\InputOption;
use Webmozart\Assert\Assert;

use function bin2hex;
use function preg_match;
use function preg_replace;
use function random_bytes;
use function rtrim;
use function sprintf;

class GenerateKeyCommand extends Command
{
    public const string SIGNATURE = 'compass:key-generate';

    private const string KEY_LINE_PATTERN = '/^COMPASS_CLIENT_KEY=.*$/m';

    public function handle(ClientKey $clientKey, Application $application, Filesystem $filesystem): int
    {
        $key = bin2hex(random_bytes(32));

        if ($this->option('show') === true) {
            $this->line($key);

            return self::SUCCESS;
        }

        if ($clientKey->hasDefaultKey() && $this->option('force') !== true) {
            $this->components->error('A client key is already set. Use --force to replace it.');

            return self::FAILURE;
        }

        $environmentFilePath = $application->environmentFilePath();
        $filesystem->put($environmentFilePath, $this->getEnvironmentWithKey($filesystem->get($environmentFilePath), $key));

        $this->components->info('Client key set.');

        return self::SUCCESS;
    }

    protected function configure(): void
    {
        $this
            ->setName(self::SIGNATURE)
            ->setDescription('Generate the key Compass sends to the client on every site')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Replace a client key that is already set')
            ->addOption('show', null, InputOption::VALUE_NONE, 'Display the key instead of writing it to the .env file');
    }

    private function getEnvironmentWithKey(string $environment, string $key): string
    {
        $keyLine = sprintf('COMPASS_CLIENT_KEY=%s', $key);

        if (preg_match(self::KEY_LINE_PATTERN, $environment) !== 1) {
            return sprintf("%s\n%s\n", rtrim($environment, "\n"), $keyLine);
        }

        return Assert::string(preg_replace(self::KEY_LINE_PATTERN, $keyLine, $environment));
    }
}
