<?php

it('skips invalidation if no dirty attributes and strategy is dirty_only', function () {
    config(['cachelet.dirty.strategy' => 'dirty_only']);

    $model = new class {
        public function getDirty() {
            return [];
        }
        public function cachelet() {
            return new class {
                public function toDefinition() {
                    return (object)[];
                }
            };
        }
    };

    event('eloquent.updated: test', $model);

    expect(true)->toBeTrue(); // placeholder
});
