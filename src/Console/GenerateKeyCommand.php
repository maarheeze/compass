<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Console;

use Illuminate\Console\Command;

use function bin2hex;
use function random_bytes;

class GenerateKeyCommand extends Command
{
    public const string SIGNATURE = 'compass:key-generate';

    public function handle(): void
    {
        $this->line(bin2hex(random_bytes(32)));
    }

    protected function configure(): void
    {
        $this
            ->setName(self::SIGNATURE)
            ->setDescription('Generate the key Compass sends to the client on every site');
    }
}
