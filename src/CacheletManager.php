<?php

namespace Garaekz\Cachelet;

use Garaekz\Cachelet\Builders\CacheletBuilder;
use Garaekz\Cachelet\Builders\ModelCacheletBuilder;
use Garaekz\Cachelet\Contracts\CacheletBuilderInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Traits\Macroable;

class CacheletManager
{
    use Macroable;

    protected array $config;

    protected array $builders = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function for(string $prefix): CacheletBuilderInterface
    {
        return $this->createBuilder(CacheletBuilder::class, $prefix);
    }

    public function forModel(Model $model, ?string $prefix = null): CacheletBuilderInterface
    {
        $prefix = $prefix ?? $this->getModelPrefix($model);

        $builder = $this->createModelBuilder($prefix);

        return $builder->setModel($model)
            ->from($this->getModelCacheableAttributes($model));
    }

    protected function createModelBuilder(string $prefix): ModelCacheletBuilder
    {
        return new ModelCacheletBuilder($prefix, $this->config);
    }

    public function forPrefixModel(string $prefix, Model $model): CacheletBuilderInterface
    {
        return $this->forModel($model, $prefix);
    }

    protected function createBuilder(string $builderClass, string $prefix): CacheletBuilderInterface
    {
        return new $builderClass($prefix, $this->config);
    }

    protected function getModelPrefix(Model $model): string
    {
        $table = $model->getTable();
        $key = $model->getKey();

        return "{$table}:{$key}";
    }

    protected function getModelCacheableAttributes(Model $model): array
    {
        if (method_exists($model, 'getCacheableAttributes')) {
            return $model->getCacheableAttributes();
        }

        return $model->getAttributes();
    }
}
