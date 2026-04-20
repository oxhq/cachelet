<?php

use Oxhq\Cachelet\Testing\ExpectCachelet;

function expectCachelet(string $key): ExpectCachelet
{
    return new ExpectCachelet($key);
}
