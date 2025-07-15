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

    protected $dates = ['created_at', 'updated_at', 'email_verified_at'];
}
