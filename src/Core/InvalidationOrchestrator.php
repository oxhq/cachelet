<?php

namespace Garaekz\Cachelet\Support;

use Garaekz\Cachelet\ValueObjects\CacheletDefinition;
use Garaekz\Cachelet\Strategies\TokenGenerationStrategy;
use Garaekz\Cachelet\Strategies\TagInvalidationStrategy;
use Garaekz\Cachelet\Strategies\WildcardInvalidationStrategy;

class InvalidationOrchestrator
{
    protected array $strategies;

    public function __construct()
    {
        $this->strategies = [
            'token' => new TokenGenerationStrategy(),
            'tags' => new TagInvalidationStrategy(),
            'wildcard' => new WildcardInvalidationStrategy(),
        ];
    }

    public function invalidate(CacheletDefinition $definition): void
    {
        $strategyKey = config('cachelet.driver.strategy.default', 'token');

        if (isset($this->strategies[$strategyKey])) {
            $this->strategies[$strategyKey]->invalidate($definition);
        }
    }

    public function forget(CacheletDefinition $definition): void
    {
        $this->invalidate($definition);
    }
}
