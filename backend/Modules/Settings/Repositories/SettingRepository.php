<?php

namespace Modules\Settings\Repositories;

use Modules\Settings\Models\Setting;

/**
 * Database access for settings. Intentionally cache-free: caching happens one
 * layer up, in SettingService, so this class stays trivially testable.
 */
class SettingRepository
{
    /**
     * All rows as a key => value map.
     *
     * @return array<string, mixed>
     */
    public function fetchAll(): array
    {
        return Setting::query()
            ->get(['key', 'value'])
            ->mapWithKeys(fn (Setting $setting) => [$setting->key => $setting->value])
            ->all();
    }

    public function findByKey(string $key): mixed
    {
        return Setting::query()->where('key', $key)->first()?->value;
    }

    public function set(string $key, mixed $value): Setting
    {
        return Setting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    public function deleteByKey(string $key): bool
    {
        return (bool) Setting::query()->where('key', $key)->delete();
    }
}
