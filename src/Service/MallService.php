<?php

namespace App\Service;

use App\Exception\SystemException;
use App\Medoo\Repository\MallInfoRepository;
use App\Middleware\Context;
use App\Service\Cafe24Api\OAuth;
use App\Utils\ArrayUtil;
use Exception;
use Psr\Log\LoggerInterface;

class MallService
{
    protected LoggerInterface $appLogger;
    private MallInfoRepository $mallInfoRepository;
    private OAuth $OAuth;

    public function __construct(
        LoggerInterface       $appLogger,
        MallInfoRepository    $mallInfoRepository,
        OAuth                 $OAuth
    )
    {
        $this->appLogger = $appLogger;
        $this->mallInfoRepository = $mallInfoRepository;
        $this->OAuth = $OAuth;
    }

    public function getAccessToken1(): string
    {
        $accessToken = $this->getAccessToken();

        if (strlen($accessToken) == 0) {
            throw new SystemException('access_token이 없습니다.');
        }

        return $accessToken;
    }

    public function getAccessToken(): string
    {
        $mallInfo = $this->getMallInfo();
        $accessToken = ArrayUtil::getVal('access_token', $mallInfo);
        // refresh_token이 없으면 아래 실행안함
        $refreshToken = ArrayUtil::getVal('refresh_token', $mallInfo);
        if (!$refreshToken) return $accessToken;

        $expiresDate = ArrayUtil::getVal('expires_date', $mallInfo);
        try {
            $expiresDateTime = strtotime($expiresDate);
        } catch (Exception) {
            $expiresDateTime = strtotime('1970-01-01 00:00:00');
        }
        $currentDateTime = time();
        // 토큰 유효기간이 아직 지나지 않았으면 토큰정보 그대로 사용
        if ($expiresDateTime > $currentDateTime) return $accessToken;

        $refreshDate = ArrayUtil::getVal('refresh_expires_date', $mallInfo);
        try {
            $refreshDateTime = strtotime($refreshDate);
        } catch (Exception) {
            $refreshDateTime = strtotime('1970-01-01 00:00:00');
        }
        // 리프래시 토큰 유효기간이 아직 지나지 않았으면 토큰을 리프래시한 후 조회한 데이터를 디비에 저장해줌
        if ($refreshDateTime > $currentDateTime) {
            $tokenInfo =  $this->OAuth->send([
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken
            ]);

            // 리턴한 값이 문제있으면 원 토큰 리턴한다
            if (ArrayUtil::isValidArray($tokenInfo) === false) {
                return '';
            }

            $result = $this->updateMallInfo($tokenInfo);

            // 데이터 처리 실패하면 빈값을 리턴
            if ($result === false) return '';

            return ArrayUtil::getVal('access_token', $tokenInfo);
        }
        // 리프래시 토큰도 만료되면 그냥 빈값을 반환
        return '';
    }

    public function setMallInfo(array $tokenInfo): bool
    {
        $flag = $this->mallInfoRepository->count(['mall_id' => Context::getMallId()]);
        if ($flag === 0) {
            $result = $this->insertMallInfo($tokenInfo);
        } else{
            $result = $this->updateMallInfo($tokenInfo);
        }
        return $result;
    }

    public function getMallInfo(): ?array
    {
        $mallInfo = $this->mallInfoRepository->findOneBy(['mall_id' => Context::getMallId()]);

        if (ArrayUtil::isValidArray($mallInfo)) return $mallInfo;
        return [];
    }

    public function insertMallInfo(array $tokenInfo): bool
    {
        $insertData = [
            'mall_id' => Context::getMallId(),
            'access_token' => ArrayUtil::getVal('access_token', $tokenInfo),
            'expires_date' => ArrayUtil::getVal('expires_at', $tokenInfo),
            'refresh_token' => ArrayUtil::getVal('refresh_token', $tokenInfo),
            'refresh_expires_date' => ArrayUtil::getVal('refresh_token_expires_at', $tokenInfo),
            'create_date' => date('Y-m-d H:i:s'),
            'issued_date' => ArrayUtil::getVal('issued_at', $tokenInfo),
            'credit' => 0,
            'is_used' => 'T'
        ];
        $dbResult = $this->mallInfoRepository->insert($insertData);

        // 처리결과 -1이면 실패 리턴
        if ($dbResult === -1) return false;

        return true;
    }

    public function updateMallInfo(array $tokenInfo): bool
    {
        $updateData = [
            'access_token' => ArrayUtil::getVal('access_token', $tokenInfo),
            'expires_date' => ArrayUtil::getVal('expires_at', $tokenInfo),
            'refresh_token' => ArrayUtil::getVal('refresh_token', $tokenInfo),
            'refresh_expires_date' => ArrayUtil::getVal('refresh_token_expires_at', $tokenInfo),
            'update_date' => date('Y-m-d H:i:s'),
            'issued_date' => ArrayUtil::getVal('issued_at', $tokenInfo),
            'is_used' => 'T'
        ];
        $dbResult = $this->mallInfoRepository->update($updateData, ['mall_id' => Context::getMallId()]);

        // 처리결과 1보다 적으면 실패 리턴
        if ($dbResult < 1) return false;

        return true;
    }
}