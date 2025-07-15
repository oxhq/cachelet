<?php

namespace Garaekz\Cachelet\Events;

class CacheletStored
{
    public function __construct(
        public string $key,
        public mixed $value
    ) {}
}
