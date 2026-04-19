<?php

declare(strict_types=1);

namespace Tests\Models;

use Garaekz\Cachelet\Traits\UsesCachelet;
use Illuminate\Database\Eloquent\Model;

class Dummy extends Model
{
    use UsesCachelet;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'email_verified_at' => 'datetime',
        ];
    }
}
