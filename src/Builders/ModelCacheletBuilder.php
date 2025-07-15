<?php

namespace Garaekz\Cachelet\Builders;

use Garaekz\Cachelet\Events\CacheletInvalidated;
use Illuminate\Database\Eloquent\Model;

class ModelCacheletBuilder extends CacheletBuilder
{
    protected ?Model $model = null;

    public function setModel(Model $model): static
    {
        $this->model = $model;
        $this->withTags([
            'model:'.get_class($model),
            'model_id:'.$model->getKey(),
        ]);

        return $this;
    }

    public function invalidate(): void
    {
        parent::invalidate();

        if ($this->model && $this->shouldDispatchModelEvent()) {
            event(new CacheletInvalidated(
                $this->model,
                'manual',
                $this->key()
            ));
        }
    }

    protected function shouldDispatchModelEvent(): bool
    {
        if (method_exists($this->model, 'shouldDispatchCacheletInvalidationEvent')) {
            return $this->model->shouldDispatchCacheletInvalidationEvent('manual');
        }

        return $this->config['observability']['events']['enabled'] ?? false;
    }
}
