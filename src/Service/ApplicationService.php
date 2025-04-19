<?php

namespace App\Service;

use App\Service\Cafe24Api\OAuth;
use App\Utils\AppAuthUtil;
use App\Utils\ArrayUtil;
use App\Utils\Env;
use Psr\Log\LoggerInterface;

class ApplicationService
{
    protected LoggerInterface $appLogger;
    private OAuth $OAuth;

    public function __construct(
        LoggerInterface $appLogger,
        OAuth $OAuth,
    )
    {
        $this->appLogger = $appLogger;
        $this->OAuth = $OAuth;
    }

    public function authValid($params): string
    {
        $errMsg = AppAuthUtil::checkValidData($params, 'mall_id');
        if ($errMsg) return $errMsg;

        $errMsg = AppAuthUtil::checkValidData($params, 'hmac');
        if ($errMsg) return $errMsg;

        $errMsg = AppAuthUtil::checkHmac($params);
        if ($errMsg) return $errMsg;

        $errMsg = AppAuthUtil::checkTime($params);
        if ($errMsg) return $errMsg;

        return '';
    }

    public function auth($params): string
    {
        // redirect url 을 조합해 줌
        $requestUrl = 'https://{mall_id}.cafe24api.com/api/v2/oauth/authorize?response_type=code&client_id={client_id}&state={encode_csrf_token}&redirect_uri={encode_redirect_uri}&scope={scope}';
        $redirectUri = Env::get('APP_BACK_HOST') . '/api/application/redirect';
        $replaceData = [
            'mall_id' => $params['mall_id'],
            'client_id' => Env::get('APP_CLIENT_ID'),
            'encode_csrf_token' => base64_encode(json_encode($params)),
            'encode_redirect_uri' => urlencode($redirectUri),
            'scope' => Env::get('APP_SCOPE'),
        ];

        foreach ($replaceData as $key => $value) {
            $requestUrl = str_replace('{' . $key . '}', $value, $requestUrl);
        }

        return $requestUrl;
    }

    public function redirectValid($params): string
    {
        // 4번의 체크를 진행하여 에러메세지가 모두 있으면 에러메세지 리턴
        $errMsg = AppAuthUtil::checkValidData($params, 'code');
        if ($errMsg) return $errMsg;

        $errMsg = AppAuthUtil::checkValidData($params, 'state');
        if ($errMsg) return $errMsg;

        $state = json_decode(base64_decode($params['state']), true);
        if ($state == null) return 'state error';

        $errMsg = AppAuthUtil::checkHmac($state);
        if ($errMsg) return $errMsg;

        $errMsg = AppAuthUtil::checkTime($state);
        if ($errMsg) return $errMsg;

        return '';
    }

    public function getTokenByCode($code): array
    {
        return $this->OAuth->send([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => Env::get('APP_BACK_HOST') . '/api/application/redirect'
        ]);
    }

    public function checkValidToken($tokenInfo): bool
    {
        $userId = ArrayUtil::getVal('jti', $tokenInfo);
        // 아이디가 없다면 유효한 토큰이 아님
        if (!$userId) return false;

        return true;
    }
}
