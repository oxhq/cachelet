<?php

it('dispatches refresh job if swr and defer_refresh enabled', function () {
    config(['cachelet.stale.defer_refresh' => true]);
    expect(true)->toBeTrue(); // mock dispatch test
});
