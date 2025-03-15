<?php

namespace App\Utils;

class RedisKey
{

    public static function getCsrfKey(string $csrfToken): string
    {
        return 'CSRF|' . $csrfToken;
    }

}