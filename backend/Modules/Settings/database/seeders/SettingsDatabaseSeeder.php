<?php

namespace Modules\Settings\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Settings\Repositories\SettingRepository;
use Modules\Settings\Services\SettingService;
use Modules\Settings\Services\SettingsSchema;

/**
 * Writes the shipped defaults into the settings table.
 *
 * Existing rows are left untouched, so re-running the seeder after a deploy
 * only adds groups that were introduced since - it never overwrites content
 * an admin has edited.
 */
class SettingsDatabaseSeeder extends Seeder
{
    public function run(SettingRepository $repository, SettingService $service): void
    {
        $existing = $repository->fetchAll();

        foreach (SettingsSchema::defaults() as $key => $value) {
            if (array_key_exists($key, $existing)) {
                continue;
            }

            $repository->set($key, $value);
        }

        $service->flush();
    }
}
