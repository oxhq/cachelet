<?php

namespace Garaekz\Cachelet\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CacheletListCommand extends Command
{
    protected $signature = 'cachelet:list {--prefix=}';

    protected $description = 'List cachelet keys by prefix';

    public function handle(): int
    {
        $prefix = $this->option('prefix') ?? 'generic';
        $set = Cache::getStore()->smembers("cachelet:registry:$prefix");

        foreach ($set as $key) {
            $this->line("- $key");
        }

        return 0;
    }
}
