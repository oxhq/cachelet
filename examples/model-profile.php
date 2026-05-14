<?php

$profile = $user->cachelet()
    ->exclude(['updated_at'])
    ->ttl(300)
    ->remember(fn () => $user->fresh());

$profileWithTimestamps = $user->cachelet()
    ->withTimestamps()
    ->remember(fn () => $user->fresh());
