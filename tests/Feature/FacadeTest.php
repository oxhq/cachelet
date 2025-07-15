<?php

use Garaekz\Cachelet\Facades\Cachelet;

it('facade resolves properly', function () {
    $builder = Cachelet::for('users');
    expect($builder)->toBeInstanceOf(Garaekz\Cachelet\Core\CacheletBuilder::class);
});
