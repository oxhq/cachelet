<?php

namespace Garaekz\Cachelet\Console\Commands;

use Garaekz\Cachelet\Support\CoordinateLogger;
use Illuminate\Console\Command;

class CacheletFlushCommand extends Command
{
    protected $signature = 'cachelet:flush {prefix}';

    protected $description = 'Flush cachelet keys for a prefix';

    public function handle(CoordinateLogger $logger): int
    {
        foreach ($logger->flush((string) $this->argument('prefix')) as $key) {
            $this->line("Deleted: {$key}");
        }

        return self::SUCCESS;
    }
}
