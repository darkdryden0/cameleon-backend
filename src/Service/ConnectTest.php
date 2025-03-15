<?php

namespace App\Service;

use App\Utils\Env;
use Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class ConnectTest
{
    private Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    public function testRedis(): string
    {
        try {
            $this->redis->set('test', '111', 30);
            $this->redis->del('test');
            return 'success';
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function testMq(): string
    {
        try {
            new AMQPStreamConnection(
                Env::get('RABBITMQ_HOST'),
                Env::get('RABBITMQ_PORT'),
                Env::get('RABBITMQ_USER'),
                Env::get('RABBITMQ_PASSWORD')
            );
            return 'success';
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}