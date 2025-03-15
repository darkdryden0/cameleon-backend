<?php

namespace App\Utils;

class AppAuthUtil
{
    /**
     * 파람체크
     * @param $data
     * @param $key
     * @return string
     */
    public static function checkValidData($data, $key): string
    {
        if (is_array($data) === false) return 'param does not exist!!';

        if (ArrayUtil::arrayKeyExists($key, $data) === false) return $key . ' does not set!!';

        if (is_string($data[$key]) === false) return $key . ' type is illegal!!';

        return '';
    }

    /**
     * hmac 체크
     * @param $params
     * @return string
     */
    public static function checkHmac($params): string
    {
        $errMsg = '';
        if (ArrayUtil::arrayKeyExists('userParams', $params)) return $errMsg;

        $hmac = $params['hmac'];
        unset($params['hmac']);
        $secret_key = Env::get('APP_SECRET_KEY');

        ksort($params);

        $data = '';
        foreach ($params as $key => $value) {
            $value = str_replace(' ', '%20', rawurlencode($value));
            $data .= $key . '=' . $value . '&';
        }
        $data = rtrim($data, '&');

        $hash = base64_encode(hash_hmac('sha256', $data, $secret_key, true));

        if ($hash !== $hmac) {
            $errMsg = 'hmac error!!';
        }
        return $errMsg;
    }

    /**
     * 시간 초과하였는지 체크
     * @param $params
     * @return string
     */
    public static function checkTime($params): string
    {
        $errMsg = '';

        $timestamp = $params['timestamp'];
        $now = time();
        if ($now - $timestamp > 120 || $now - $timestamp < -120) {
            $errMsg = 'time out!!';
        }
        return $errMsg;
    }
}