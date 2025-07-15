<?php

namespace Garaekz\Cachelet\Support;

use Garaekz\Cachelet\Core\CacheletBuilder;
use Garaekz\Cachelet\Support\CacheDriverCapabilities;
use Illuminate\Database\Eloquent\Model;

class Cachelet
{
    public static function for(string|array $context): CacheletBuilder
    {
        return (new CacheletBuilder())->from($context);
    

    

    public static function forModel(Model $model): CacheletBuilder
    {
        return (new CacheletBuilder())->fromModel($model);
    

    

    public static function capabilities(): array
    {
        return (new CacheDriverCapabilities())->detect();

public static function inspect(string $prefix): array
    {
        return (new \Garaekz\Cachelet\Core\CoordinateLogger)->inspect($prefix);
    }
}