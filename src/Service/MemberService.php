<?php

namespace App\Service;

use App\Medoo\Repository\MemberInfoRepository;
use App\Middleware\Context;
use App\Utils\ArrayUtil;
use Psr\Log\LoggerInterface;

class MemberService
{
    protected LoggerInterface $appLogger;
    private MemberInfoRepository $memberLoginRepository;

    public function __construct(
        LoggerInterface       $appLogger,
        MemberInfoRepository $memberLoginRepository
    )
    {
        $this->appLogger = $appLogger;
        $this->memberLoginRepository = $memberLoginRepository;
    }

    public function checkEmail($param): bool
    {
        $where = [
            'email' => ArrayUtil::getVal('email', $param),
        ];
        $dbResult = $this->memberLoginRepository->count($where);
        // 이메일이 이미 존재한다면 실패 리턴
        if ($dbResult > 0) return false;
        return true;
    }

    public function registerMember($param): bool
    {
        $data = [
            'email' => ArrayUtil::getVal('email', $param),
            'password' => ArrayUtil::getVal('password', $param),
            'mall_id' => Context::getMallId(),
        ];
        $dbResult = $this->memberLoginRepository->insert($data);
        // 처리결과 -1이면 실패 리턴
        if ($dbResult === -1) return false;
        return true;
    }

    public function checkLogin($param): bool
    {
        $where = [
            'email' => ArrayUtil::getVal('email', $param),
            'password' => ArrayUtil::getVal('password', $param),
            'mall_id' => Context::getMallId(),
        ];
        $dbResult = $this->memberLoginRepository->count($where);
        // 검색결과 1개이하면 실패 리턴
        if ($dbResult < 1) return false;
        return true;
    }

    public function getMemberInfo(): ?array
    {
        $userInfo = $this->memberLoginRepository->findOneBy(['user_id' => Context::getUserId()]);

        if (ArrayUtil::isValidArray($userInfo)) return $userInfo;
        return [];
    }
}