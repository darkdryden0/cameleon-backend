<?php

namespace App\Utils;

class Env
{
    public static function get(string $key)
    {
        if (getenv($key) === false) {
            return array_key_exists($key, $_ENV) ? $_ENV[$key] : '';
        }

        return getenv($key);
    }

    public static function isProd(): bool
    {
        return self::get('APP_ENV') === 'prod';
    }

    public static function isDev(): bool
    {
        return in_array(self::get('APP_ENV'), ['local', 'dev']);
    }
}