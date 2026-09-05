<?php

namespace Modules\Settings\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Settings\Repositories\SettingRepository;

/**
 * Read/write facade for site settings.
 *
 * Reads are served from a single cache entry holding every group merged over
 * its defaults, so rendering a page costs zero database queries no matter how
 * many groups a template touches. Writes go straight to the database and then
 * drop that one entry.
 */
class SettingService
{
    public function __construct(
        protected SettingRepository $repository,
        protected SettingImageHandler $imageHandler,
    ) {}

    protected function cacheKey(): string
    {
        return (string) config('settings.cache_key', 'site_settings');
    }

    protected function ttl(): int
    {
        return (int) config('settings.cache_ttl', 86400);
    }

    /**
     * Every settings group, defaults filled in for anything unsaved.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return Cache::remember($this->cacheKey(), $this->ttl(), function () {
            $stored = $this->repository->fetchAll();

            $merged = [];

            foreach (SettingsSchema::defaults() as $group => $default) {
                $value = $stored[$group] ?? null;

                // Shallow merge on purpose: top-level keys of a group fall
                // back to their default (so a newly added field appears
                // without a re-save), while list-shaped values such as
                // menu_items or slides are replaced wholesale - an admin who
                // deletes a slide must not get it back from the defaults.
                $merged[$group] = is_array($value)
                    ? array_replace($default, $value)
                    : $default;
            }

            // Keep any legacy or custom key that is not part of the schema so
            // nothing silently disappears from the API.
            foreach ($stored as $group => $value) {
                if (! array_key_exists($group, $merged)) {
                    $merged[$group] = $value;
                }
            }

            return $merged;
        });
    }

    /**
     * One group, defaults applied.
     *
     * @return array<string, mixed>|mixed
     */
    public function find(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default ?? SettingsSchema::defaultFor($key);
    }

    /**
     * Dot-path lookup inside the merged payload, e.g. "footer.contact.email".
     */
    public function value(string $path, mixed $default = null): mixed
    {
        return data_get($this->all(), $path, $default);
    }

    /**
     * Persist one group. Any inline base64 image inside the payload is moved
     * to disk first and replaced by its public URL.
     */
    public function set(string $key, mixed $value): array
    {
        $previous = $this->repository->findByKey($key);

        $processed = $this->imageHandler->process($value, $previous);

        $this->repository->set($key, $processed);

        $this->flush();

        return is_array($processed) ? $processed : ['value' => $processed];
    }

    /**
     * Persist several groups in one round trip; used by the settings editor,
     * which saves a whole tab at once.
     *
     * @param  array<string, mixed>  $groups
     * @return array<string, mixed>
     */
    public function setMany(array $groups): array
    {
        $saved = [];

        foreach ($groups as $key => $value) {
            if (! SettingsSchema::isGroup($key)) {
                continue;
            }

            $previous = $this->repository->findByKey($key);
            $processed = $this->imageHandler->process($value, $previous);

            $this->repository->set($key, $processed);
            $saved[$key] = $processed;
        }

        $this->flush();

        return $saved;
    }

    /**
     * Reset a group back to its shipped default.
     */
    public function reset(string $key): array
    {
        $this->repository->deleteByKey($key);
        $this->flush();

        return SettingsSchema::defaultFor($key);
    }

    public function delete(string $key): bool
    {
        $deleted = $this->repository->deleteByKey($key);
        $this->flush();

        return $deleted;
    }

    /**
     * Drop the cached payload. Called after every write so the next read
     * rebuilds it from the database.
     */
    public function flush(): void
    {
        Cache::forget($this->cacheKey());
    }
}
