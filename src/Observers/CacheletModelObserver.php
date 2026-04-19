<?php

namespace Garaekz\Cachelet\Observers;

use Illuminate\Database\Eloquent\Model;

class CacheletModelObserver
{
    public function saved(Model $model): void
    {
        $this->invalidate($model, 'saved');
    }

    public function deleted(Model $model): void
    {
        $this->invalidate($model, 'deleted');
    }

    public function updated(Model $model): void
    {
        $this->invalidate($model, 'updated');
    }

    protected function invalidate(Model $model, string $reason): void
    {
        if (! method_exists($model, 'cachelet')) {
            return;
        }

        $model->cachelet()->invalidatePrefix($reason);
    }
}
