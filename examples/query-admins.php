<?php

$admins = User::query()
    ->where('role', 'admin')
    ->cachelet()
    ->ttl(300)
    ->rememberQuery();
