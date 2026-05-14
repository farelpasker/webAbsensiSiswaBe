<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type'];

    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        if (!$setting) {
            return $default;
        }

        // Cast value based on type
        return match($setting->type) {
            'integer' => (int)$setting->value,
            'decimal' => (float)$setting->value,
            'json' => json_decode($setting->value, true),
            'boolean' => (bool)$setting->value,
            default => $setting->value,
        };
    }

    public static function set($key, $value, $type = 'string')
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $type === 'json' ? json_encode($value) : $value, 'type' => $type]
        );
    }
}
