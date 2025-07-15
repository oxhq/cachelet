<?php

namespace Garaekz\Cachelet\Strategies;

use Garaekz\Cachelet\ValueObjects\CacheletDefinition;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TokenGenerationStrategy
{
    public function invalidate(CacheletDefinition $definition): void
    {
        if (!isset($definition->context['model'])) {
            return;
        }

        $model = $definition->context['model'];
        $table = $model->getTable();
        $id = $model->getKey();

        if (!$id) {
            return;
        }

        $tokenKey = $this->tokenKey($table, $id);
        Cache::increment($tokenKey);
    }

    public function getTokenFor($table, $id): int
    {
        $tokenKey = $this->tokenKey($table, $id);
        return Cache::get($tokenKey, 1);
    }

    protected function tokenKey(string $table, int|string $id): string
    {
        return "token:{$table}:{$id}";
    }
}
