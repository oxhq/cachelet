<?php

use Illuminate\Support\Facades\Cache;

trait Invalidatable
{
    public function invalidate(): void
    {
        Cache::forget($this->key());
    }

    public function forget(): void
    {
        $this->invalidate();
    }
}
