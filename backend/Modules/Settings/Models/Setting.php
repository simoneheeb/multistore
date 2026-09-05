<?php

namespace Modules\Settings\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A single settings group. Reads should go through SettingService, which
 * caches; this model is the storage layer only.
 */
class Setting extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'settings';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    protected static function newFactory()
    {
        return \Modules\Settings\Database\Factories\SettingFactory::new();
    }
}
