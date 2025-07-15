<?php

namespace Garaekz\Cachelet\Core;

use Garaekz\Cachelet\ValueObjects\CacheletDefinition;
use Illuminate\Support\Str;

class KeyHasher
{
    public function make(CacheletDefinition $definition): string
    {
        $tokens = $this->collectTokens($definition);

        $prefix = $this->resolvePrefix($definition);

        return 'cachelet:'.$prefix.':'.md5(implode(':', $tokens));
    }

    protected function collectTokens(CacheletDefinition $definition): array
    {
        $segments = [];

        foreach ($definition->context as $key => $value) {
            $segments[] = is_int($key) ? $value : "{$key}:{$value}";
        }

        return $segments;
    }

    protected function resolvePrefix(CacheletDefinition $definition): string
    {
        if ($definition->prefix) {
            return Str::slug($definition->prefix, '_');
        }

        // Try to infer from model
        if (isset($definition->context['model'])) {
            return Str::snake(class_basename($definition->context['model']));
        }

        return 'generic';
    }
}
