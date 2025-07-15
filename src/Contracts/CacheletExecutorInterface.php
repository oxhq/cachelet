<?php

namespace Garaekz\Cachelet\Contracts;

use Garaekz\Cachelet\ValueObjects\CacheletDefinition;

interface CacheletExecutorInterface
{
    public function execute(CacheletDefinition $definition): mixed;
}
