<?php

namespace Garaekz\Cachelet\Observers;

use Garaekz\Cachelet\Core\InvalidationOrchestrator;
use Illuminate\Database\Eloquent\Model;

class CacheletModelObserver
{
    public function saved(Model $model): void
    {
        $this->invalidate($model);
    }

    public function deleted(Model $model): void
    {
        $this->invalidate($model);
    }

    public function updated(Model $model): void
    {
        $this->invalidate($model);
    }

    protected function shouldInvalidate(Model $model): bool
    {
        if (! method_exists($model, 'getDirty')) {
            return true;
        }
        $dirty = array_keys($model->getDirty());

        return config('cachelet.dirty.strategy') !== 'dirty_only'
            || ! empty($dirty);
    }

    protected function invalidate(Model $model): void
    {
        if (! method_exists($model, 'cachelet')) {
            return;
        }

        if (! $this->shouldInvalidate($model)) {
            return;
        }

        app(InvalidationOrchestrator::class)->invalidate(
            $model->cachelet()->toDefinition()
        );
    }
}
