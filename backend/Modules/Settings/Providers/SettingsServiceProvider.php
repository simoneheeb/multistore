<?php

namespace Modules\Settings\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class SettingsServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Settings';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'settings';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Publish the settings schema defaults under their own config namespace.
     *
     * The module loader only picks up config/config.php, so the (much
     * larger) defaults file is registered explicitly here and read back as
     * config('settings-defaults') by SettingsSchema.
     */
    public function register(): void
    {
        parent::register();

        $this->mergeConfigFrom(
            module_path($this->name, 'config/defaults.php'),
            'settings-defaults'
        );
    }

    /**
     * Define module schedules.
     * 
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
