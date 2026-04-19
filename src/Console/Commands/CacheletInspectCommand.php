<?php

namespace Garaekz\Cachelet\Console\Commands;

use Garaekz\Cachelet\Support\CoordinateLogger;
use Illuminate\Console\Command;

class CacheletInspectCommand extends Command
{
    protected $signature = 'cachelet:inspect {prefix}';

    protected $description = 'Inspect cachelet metadata for a prefix';

    public function handle(CoordinateLogger $logger): int
    {
        foreach ($logger->inspect((string) $this->argument('prefix')) as $metadata) {
            $this->line(json_encode($metadata, JSON_UNESCAPED_SLASHES));
        }

        return self::SUCCESS;
    }
}
