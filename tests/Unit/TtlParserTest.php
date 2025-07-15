<?php

use Garaekz\Cachelet\Core\TtlParser;

it('parses string durations', function () {
    $parser = new TtlParser;
    expect($parser->parse('30 minutes'))->toBeInt();
});

it('throws on invalid strings', function () {
    $parser = new TtlParser;
    $parser->parse('invalid'); // Should throw
})->throws(InvalidArgumentException::class);
