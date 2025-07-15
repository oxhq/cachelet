<?php

declare(strict_types=1);

namespace Garaekz\Cachelet;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @extends Cachelet<ModelCachelet>
 */
class ModelCachelet extends Cachelet
{
    protected Model $model;

    protected array $exclude = [];

    protected array $only = [];

    protected array $includeDates = [];

    protected bool $includeAllDates = false;

    protected bool $includeTimestamps = false;

    public static function forModel(Model $model): static
    {
        $prefix = method_exists($model, 'getCacheletPrefix')
            ? $model->getCacheletPrefix()
            : Str::snake(class_basename($model));

        $instance = new static($prefix);
        $instance->model = $model;

        return $instance;
    }

    public function exclude(array $fields): static
    {
        $this->exclude = array_merge($this->exclude, $fields);

        return $this;
    }

    public function only(array $fields): static
    {
        $this->only = $fields;

        return $this;
    }

    /**
     * Include date attributes in serialization.
     * If no fields passed, includes all date-like attributes.
     */
    public function withDates(array $fields = []): static
    {
        if (empty($fields)) {
            $this->includeAllDates = true;
        } else {
            $this->includeDates = array_merge($this->includeDates, $fields);
        }

        return $this;
    }

    /**
     * Include the created_at and updated_at timestamps.
     */
    public function withTimestamps(): static
    {
        $this->includeTimestamps = true;

        return $this;
    }

    /**
     * @template T
     *
     * @param  Closure():T|null  $callback
     * @return T|mixed|null
     */
    public function fetch(?Closure $callback = null): mixed
    {
        // build payload before fetching
        $this->build();

        return parent::fetch($callback);
    }

    /**
     * Flush cache and dispatch invalidation event, ensuring payload built.
     */
    public function invalidate(): void
    {
        $this->build();
        parent::invalidate();
    }

    /**
     * Build payload from the Eloquent model + fluent rules + config.
     */
    public function build(): static
    {
        $cfg = config('cachelet.serialization', []);
        $excludeDatesConfig = $cfg['exclude_dates'] ?? true;
        $defaultExcludes = $cfg['default_excludes'] ?? [];
        $defaultOnly = $cfg['default_only'] ?? [];

        // If timestamps should be included, remove them from defaultExcludes
        if ($this->includeTimestamps) {
            $defaultExcludes = array_diff($defaultExcludes, ['created_at', 'updated_at']);
        }

        $attrs = $this->model->getAttributes();
        $allDates = $this->model->getDates();

        // 1) Hard override via only()
        if (! empty($this->only)) {
            $payload = collect($attrs)
                ->only($this->only)
                ->filter(fn ($v) => $v !== null)
                ->toArray();

            return $this->from($payload);
        }

        // 2) Global default-only override
        if (empty($this->only) && ! empty($defaultOnly)) {
            $payload = collect($attrs)
                ->only($defaultOnly)
                ->filter(fn ($v) => $v !== null)
                ->toArray();

            return $this->from($payload);
        }

        // 3) Exclusion flow
        $collection = collect($attrs)
            ->reject(function ($value, $key) use (
                $excludeDatesConfig,
                $defaultExcludes

            ) {
                // explicit exclude()
                if (in_array($key, $this->exclude, true)) {
                    return true;
                }

                // default config excludes
                if (in_array($key, $defaultExcludes, true)) {
                    return true;
                }

                // date-type exclusion
                if ($excludeDatesConfig && $value instanceof \DateTimeInterface) {
                    // allow timestamps if flagged
                    if (in_array($key, ['created_at', 'updated_at'], true)
                        && $this->includeTimestamps
                    ) {
                        return false;
                    }
                    // allow all dates if flagged
                    if ($this->includeAllDates) {
                        return false;
                    }
                    // allow specific includeDates
                    if (in_array($key, $this->includeDates, true)) {
                        return false;
                    }

                    // exclude other dates
                    return true;
                }

                return false;
            });

        $payload = $collection
            ->filter(fn ($v) => $v !== null)
            ->toArray();

        return $this->from($payload);
    }
}
