<?php

namespace Garaekz\Cachelet\Core;

use Carbon\Carbon;
use InvalidArgumentException;

class TtlParser
{
    public function parse(string|int|\DateTimeInterface|null $ttl): int
    {
        if (is_null($ttl)) {
            throw new InvalidArgumentException('TTL cannot be null');
        }

        if (is_int($ttl)) {
            return max(0, $ttl);
        }

        if ($ttl instanceof \DateTimeInterface) {
            $now = Carbon::now();
            $diff = Carbon::instance($ttl)->diffInSeconds($now, false);

            if ($diff <= 0) {
                throw new InvalidArgumentException('TTL date is in the past');
            }

            return $diff;
        }

        if (ctype_digit($ttl)) {
            return max(0, (int) $ttl);
        }

        // Coerce to relative if looks like duration
        if (preg_match('/^\d+\s+(minute|minutes|hour|hours|day|days)$/i', trim($ttl))) {
            $ttl = '+'.$ttl;
        }

        try {
            $parsed = Carbon::parse($ttl);
            $diff = $parsed->diffInSeconds(Carbon::now(), false);
            dd($parsed, $diff); // Debugging line, remove in production
            if ($diff <= 0) {
                throw new InvalidArgumentException("Parsed TTL '{$ttl}' is in the past");
            }

            return $diff;
        } catch (\Exception $e) {
            dd($e->getMessage()); // Debugging line, remove in production
            throw new InvalidArgumentException("Unable to parse TTL: {$ttl}");
        }
    }
}
