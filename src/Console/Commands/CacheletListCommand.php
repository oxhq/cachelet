<?php

namespace Garaekz\Cachelet\Console\Commands;

use Garaekz\Cachelet\Support\CoordinateLogger;
use Illuminate\Console\Command;

class CacheletListCommand extends Command
{
    protected $signature = 'cachelet:list {prefix}';

    protected $description = 'List cachelet keys for a prefix';

    public function handle(CoordinateLogger $logger): int
    {
        foreach ($logger->keys((string) $this->argument('prefix')) as $key) {
            $this->line($key);
        }

        return self::SUCCESS;
    }
}
