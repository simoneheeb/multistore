<?php

namespace Modules\Settings\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Settings\Models\Setting;

class SettingFactory extends Factory
{
    protected $model = Setting::class;

    public function definition(): array
    {
        return [
            'key'   => $this->faker->unique()->word(),
            'value' => [$this->faker->word() => $this->faker->word()],
        ];
    }
}