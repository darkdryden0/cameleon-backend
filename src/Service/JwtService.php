<?php

namespace App\Service;

use App\Utils\ArrayUtil;
use App\Utils\Env;
use DateTimeImmutable;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    const TOKEN_HOURS = 12;

    private string $key;

    public function __construct()
    {
        $this->key = Env::get('JWT_KEY');
    }

    /**
     * @param array $mallInfo
     * @return string
     */
    public function encodeJwt(array $mallInfo): string
    {
        // 현재 timestamp 얻어옴
        $time = new DateTimeImmutable();
        $now = $time->getTimestamp();

        return JWT::encode([
            "iss" => 'Cameleon ' . Env::get('APP_ENV'), // 발급자(issuer)
            "aud" => "mall", // 대상자(audience)
            "sub" => "token", // 주제(subject)
            "jti" => $mallInfo['mall_id'], // 고유 식별자
            "iat" => $now, // 발급된 시간(issued at)
            "nbf" => $now - 60, // 유효 시작 시간(not before)
            "exp" => $now + (60 * 60 * self::TOKEN_HOURS), // 만료 시간(expiration time)
            "info" => [
                'mall_id' => $mallInfo['mall_id'],
                'user_id' => $mallInfo['user_id'],
            ]
        ], $this->key, 'HS512', 'keyId');
    }

    /**
     * @throws Exception
     */
    public function decodeJwt(string $token): array
    {
        return (array)JWT::decode($token, new Key($this->key, 'HS512'));
    }

    public function encodeAdminJwt(string $adminId): string
    {
        // 현재 timestamp 얻어옴
        $time = new DateTimeImmutable();
        $now = $time->getTimestamp();

        return JWT::encode([
            "iss" => 'Cameleon AI ' . Env::get('APP_ENV'), // 발급자(issuer)
            "aud" => "admin", // 대상자(audience)
            "sub" => "admin_token", // 주제(subject)
            "jti" => $adminId, // 고유 식별자
            "iat" => $now, // 발급된 시간(issued at)
            "nbf" => $now - 60, // 유효 시작 시간(not before)
            "exp" => $now + (60 * 60 * self::TOKEN_HOURS), // 만료 시간(expiration time)
            "info" => []
        ], $this->key, 'HS512', 'keyId');
    }

}