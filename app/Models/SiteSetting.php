<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function phoneAuthEnabled(): bool
    {
        return Cache::rememberForever('site_setting.phone_auth_enabled', function (): bool {
            try {
                $value = static::query()->where('key', 'phone_auth_enabled')->value('value');

                return $value === null
                    ? (bool) config('auth_methods.phone_enabled', true)
                    : filter_var($value, FILTER_VALIDATE_BOOLEAN);
            } catch (\Throwable) {
                return (bool) config('auth_methods.phone_enabled', true);
            }
        });
    }
}
