<?php

Route::get('/users', UserIndexController::class)
    ->name('users.index')
    ->cachelet(600, [
        'vary' => [
            'query' => true,
            'headers' => ['X-Tenant'],
            'auth' => true,
        ],
        'namespace' => 'users',
    ]);
