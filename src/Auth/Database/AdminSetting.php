<?php

namespace MenqzAdmin\Admin\Auth\Database;

use Illuminate\Database\Eloquent\Model;
use MenqzAdmin\Admin\Traits\DefaultDatetimeFormat;

class AdminSetting extends Model
{
    use DefaultDatetimeFormat;

    protected $fillable = ['key', 'value'];

    public function __construct(array $attributes = [])
    {
        $connection = config('admin.database.connection') ?: config('database.default');

        $this->setConnection($connection);

        $this->setTable(config('admin.database.settings_table', 'admin_settings'));

        parent::__construct($attributes);
    }

    public static function getValue(string $key, $default = null)
    {
        try {
            $value = static::query()->where('key', $key)->value('value');
        } catch (\Throwable $e) {
            return $default;
        }

        return $value === null ? $default : $value;
    }

    public static function setValue(string $key, $value): void
    {
        try {
            static::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        } catch (\Throwable $e) {
            return;
        }
    }
}
