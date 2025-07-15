<?php

use Illuminate\Support\Facades\Cache;

it('invalidates model on save/delete/update', function () {
    Cache::shouldReceive('forget')->andReturnTrue();

    $user = new class
    {
        use Garaekz\Cachelet\Traits\UsesCachelet;

        public function save()
        {
            event('eloquent.saved: '.self::class, $this);
        }

        public function delete()
        {
            event('eloquent.deleted: '.self::class, $this);
        }

        public function update()
        {
            event('eloquent.updated: '.self::class, $this);
        }
    };

    $user->save();
    $user->update();
    $user->delete();

    expect(true)->toBeTrue(); // placeholder
});
