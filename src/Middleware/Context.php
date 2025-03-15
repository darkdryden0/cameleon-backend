<?php

namespace App\Middleware;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ReverseContainer;

class Context
{
    public static array $storageData = [
        'mall_id' => 'unknown',
        'user_id' => 'unknown',
        'ip' => '127.0.0.1',
        'trace_id' => '',
    ];

    private static ReverseContainer $reverseContainer;
    private static LoggerInterface $appLogger;
    private static string $jwtToken = '';

    public static function setMallId(string $mallId): void
    {
        self::$storageData['mall_id'] = $mallId;
    }

    public static function getMallId(): string
    {
        return self::$storageData['mall_id'];
    }

    public static function setUserId(string $userId): void
    {
        self::$storageData['user_id'] = $userId;
    }

    public static function getUserId(): string
    {
        return self::$storageData['user_id'];
    }

    public static function setIp(string $ip): void
    {
        $ip = strlen($ip) === 0 ? '127.0.0.1' : $ip;
        self::$storageData['ip'] = $ip;
    }

    public static function getIp(): string
    {
        return self::$storageData['ip'];
    }

    public static function setTraceId(string $traceId = ''): void
    {
        self::$storageData['trace_id'] = ($traceId === '') ? uniqid() : $traceId;
    }

    public static function getTraceId(): string
    {
        if (self::$storageData['trace_id'] === '') {
            self::setTraceId();
        }

        return self::$storageData['trace_id'];
    }

    public static function setContainer(ReverseContainer $container): void
    {
        self::$reverseContainer = $container;
    }

    public static function getContainer(): ReverseContainer
    {
        return self::$reverseContainer;
    }

    public static function setLogger(LoggerInterface $logger): void
    {
        self::$appLogger = $logger;
    }

    public static function getLogger(): LoggerInterface
    {
        return self::$appLogger;
    }

    public static function setJwtToken(string $jwtToken): void
    {
        self::$jwtToken = $jwtToken;
    }

    public static function getJwtToken(): string
    {
        return self::$jwtToken;
    }

}