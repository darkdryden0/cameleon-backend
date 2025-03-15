<?php

namespace App\Medoo;

use App\Utils\Env;
use Medoo\Medoo;

class MedooConnect
{
    private static ?Medoo $medoo = null;
    public static function medoo($reConnect = false): Medoo
    {
        if (self::$medoo === null || $reConnect === true) {
            self::$medoo = new Medoo([
                'type' => 'mariadb',
                'host' => Env::get('LOG_DB_HOST'),
                'port' => Env::get('LOG_DB_PORT'), // 고정
                'database' => Env::get('LOG_DB_NAME'),
                'username' => Env::get('LOG_DB_USER'),
                'password' => Env::get('LOG_DB_PASSWORD'),
            ]);
        }

        return self::$medoo;
    }

    public static function remove(): void
    {
        self::$medoo = null;
    }
}