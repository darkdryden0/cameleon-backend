<?php

namespace App\Service;

use App\Utils\RedisKey;

class CsrfToken
{
    private Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    public function getCsrfToken(): string
    {
        $csrfToken = uniqid('crsf_');
        $this->redis->set(RedisKey::getCsrfKey($csrfToken), 'T');
        return $csrfToken;
    }

    public function checkCsrfToken($csrfToken): bool
    {
        $flag = $this->redis->get(RedisKey::getCsrfKey($csrfToken));
        $this->redis->del(RedisKey::getCsrfKey($csrfToken));
        return $flag === 'T';
    }
}