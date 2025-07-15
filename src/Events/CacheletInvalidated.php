<?php

declare(strict_types=1);

namespace Garaekz\Cachelet\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CacheletInvalidated
{
    use Dispatchable, SerializesModels;

    public Model $model;

    public string $event;

    public string $key;

    public function __construct(Model $model, string $event, string $key)
    {
        $this->model = $model;
        $this->event = $event;
        $this->key = $key;
    }
}
