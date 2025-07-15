<?php

namespace Garaekz\Cachelet\Events;

class CacheletMiss
{
    public function __construct(
        public string $key
    ) {}
}
