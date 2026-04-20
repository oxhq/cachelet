<?php

namespace Oxhq\Cachelet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Traits\Macroable;
use Oxhq\Cachelet\Builders\CacheletBuilder;
use Oxhq\Cachelet\Builders\ModelCacheletBuilder;

class CacheletManager
{
    use Macroable;

    public function __construct(
        protected array $config = []
    ) {}

    public function for(string $prefix): CacheletBuilder
    {
        return new CacheletBuilder($prefix, $this->config);
    }

    public function forModel(Model $model, ?string $prefix = null): ModelCacheletBuilder
    {
        $builder = new ModelCacheletBuilder($prefix ?? $this->prefixForModel($model), $this->config);

        return $builder->setModel($model);
    }

    public function prefixForModel(Model $model): string
    {
        if (method_exists($model, 'getCacheletPrefix')) {
            return (string) $model->getCacheletPrefix();
        }

        return $model->getTable().':'.$model->getKey();
    }
}
