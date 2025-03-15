<?php

namespace App\Service;

use App\Utils\Env;
use Predis\Client;

class Redis
{
    private static ?Client $redis = null;

    private static function getSentinelClient(): Client
    {
        $sentinels = [
            'tcp://' . Env::get('REDIS_HOST') . ':' . Env::get('REDIS_PORT') . '?password=' . Env::get('REDIS_PASSWORD')
        ];

        $options = [
            'replication' => 'sentinel',
            'service' => 'mymaster',
            'parameters' => [
                'password' => Env::get('REDIS_PASSWORD'),
                'database' => 0,
                'timeout' => 1,
                'read_write_timeout' => -1,
            ],
        ];

        return new Client($sentinels, $options);
    }

    private static function getNormalClient(): Client
    {
        return new Client([
            'scheme' => 'tcp',
            'host'   => Env::get('REDIS_HOST'),
            'port'   => (int)Env::get('REDIS_PORT'),
            'password' => Env::get('REDIS_PASSWORD'),
        ]);
    }

    private static function getRedis(): Client
    {
        if (self::$redis === null) {
            if (Env::get('APP_ENV') === 'local') {
                // 로컬 환경에서는 일반 레디스 사용
                self::$redis = self::getNormalClient();
            } else {
                self::$redis = self::getSentinelClient();
            }
        }

        self::$redis->connect();

        return self::$redis;
    }

    public function set($key, $value, $ttl = 0): void
    {
        $redis = self::getRedis();
        $redis->set($key, $value);

        if ($ttl > 0) {
            $redis->expire($key, $ttl);
        }
    }

    public function get($key): ?string
    {
        $redis = self::getRedis();
        return $redis->get($key);
    }

    public function keys($key): array
    {
        $redis = self::getRedis();
        return $redis->keys($key);
    }

    public function del($key): void
    {
        $redis = self::getRedis();
        $redis->del($key);
    }
}