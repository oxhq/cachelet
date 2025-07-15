<?php

namespace Garaekz\Cachelet\Console\Commands;

use Garaekz\Cachelet\Core\CoordinateLogger;
use Illuminate\Console\Command;

class CacheletInspectCommand extends Command
{
    protected $signature = 'cachelet:inspect {prefix}';

    protected $description = 'Inspect cachelet metadata by prefix';

    public function handle(): int
    {
        $prefix = $this->argument('prefix');
        $items = (new CoordinateLogger)->inspect($prefix);

        foreach ($items as $meta) {
            $this->line(json_encode($meta));
        }

        return 0;
    }
}
