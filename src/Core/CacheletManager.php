<?php

namespace Garaekz\Cachelet\Support;

use Garaekz\Cachelet\Core\CacheletBuilder;
use Illuminate\Database\Eloquent\Model;

class CacheletManager
{
    public function start(string|array|object $base): CacheletBuilder
    {
        // TODO: Initialize builder with generic base key/context
        return new CacheletBuilder($base);
    }

    public function startFromModel(Model $model): CacheletBuilder
    {
        // TODO: Derive context from model (table, id, etc)
        return new CacheletBuilder([
            'model' => get_class($model),
            'id' => $model->getKey(),
        ]);
    }
}
