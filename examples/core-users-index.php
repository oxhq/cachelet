<?php

use Oxhq\Cachelet\Facades\Cachelet;

$users = Cachelet::for('users.index')
    ->from(['page' => request('page', 1), 'role' => request('role')])
    ->ttl('+15 minutes')
    ->remember(fn () => User::query()
        ->when(request('role'), fn ($query, $role) => $query->where('role', $role))
        ->paginate());
