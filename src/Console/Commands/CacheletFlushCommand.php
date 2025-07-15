<?php

namespace Garaekz\Cachelet\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CacheletFlushCommand extends Command
{
    protected $signature = 'cachelet:flush {--prefix=}';

    protected $description = 'Flush cachelet keys by prefix';

    public function handle(): int
    {
        $prefix = $this->option('prefix') ?? 'generic';
        $set = Cache::getStore()->smembers("cachelet:registry:$prefix");

        foreach ($set as $key) {
            Cache::forget($key);
            $this->line("Deleted: $key");
        }

        Cache::getStore()->del("cachelet:registry:$prefix");

        return 0;
    }
}
