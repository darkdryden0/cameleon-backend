<?php

namespace App\Service;

use App\Medoo\Repository\MemberInfoRepository;
use App\Middleware\Context;
use App\Utils\ArrayUtil;
use Psr\Log\LoggerInterface;

class MemberService
{
    protected LoggerInterface $appLogger;
    private MemberInfoRepository $memberInfoRepository;

    public function __construct(
        LoggerInterface      $appLogger,
        MemberInfoRepository $memberInfoRepository
    )
    {
        $this->appLogger = $appLogger;
        $this->memberInfoRepository = $memberInfoRepository;
    }

    public function checkUserId($param): bool
    {
        $where = [
            'user_id' => ArrayUtil::getVal('user_id', $param),
        ];
        $dbResult = $this->memberInfoRepository->count($where);
        // 아이디가 이미 존재한다면 실패 리턴
        if ($dbResult > 0) return false;
        return true;
    }

    public function validPassword($param): string
    {
        $password = ArrayUtil::getVal('password', $param);
        if (!$password) return '비밀번호를 입력해 주세요.';

        // 비번 정규식 체크
        if (preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/', $password) === false) {
            return '비밀번호는 8자 이상의 영어, 숫자, 특수문자 조합으로 설정해 주세요.';
        }
        return '';
    }

    public function registerMember($param): bool
    {
        $data = [
            'user_id' => ArrayUtil::getVal('user_id', $param),
            'email' => ArrayUtil::getVal('email', $param),
            'password' => password_hash(ArrayUtil::getVal('password', $param), PASSWORD_DEFAULT),
            'phone' => ArrayUtil::getVal('phone', $param),
            'company' => ArrayUtil::getVal('company', $param),
            'business_num' => ArrayUtil::getVal('business_num', $param),
            'credit' => 0,
            'create_date' => date('Y-m-d H:i:s'),
        ];
        $dbResult = $this->memberInfoRepository->insert($data);
        // 처리결과 -1이면 실패 리턴
        if ($dbResult === -1) return false;
        return true;
    }

    public function checkLogin($param): bool
    {
        $where = [
            'user_id' => ArrayUtil::getVal('user_id', $param),
        ];
        $dbResult = $this->memberInfoRepository->findOneBy($where);
        // 아이디로 찾은 결과 없으면 실패
        if (ArrayUtil::isValidArray($dbResult) === false) return false;

        // 비번 체크시 맞지 않으면 실패.
        if (password_verify(ArrayUtil::getVal('password', $param), ArrayUtil::getVal('password', $dbResult)) === false) {
            return false;
        }
        return true;
    }

    public function getMemberInfo(): ?array
    {
        $userInfo = $this->memberInfoRepository->findOneBy(['user_id' => Context::getUserId()]);

        if (ArrayUtil::isValidArray($userInfo)) return $userInfo;
        return [];
    }

    public function getMemberByEmail($email): array
    {
        return $this->memberInfoRepository->findBy(['email' => $email]);
    }

    public function modifyMember($param): string
    {
        $updateData = [
            'email' => ArrayUtil::getVal('email', $param),
            'phone' => ArrayUtil::getVal('phone', $param),
            'company' => ArrayUtil::getVal('company', $param),
            'business_num' => ArrayUtil::getVal('business_num', $param),
        ];

        $password = ArrayUtil::getVal('password', $param);
        // 비번 입력했을 경우에 처리한다.
        if ($password) {
            $checkPwd = ArrayUtil::getVal('check_pwd', $param);
            // 비번 체크하여 에러메세지 있으면 리턴한다.
            $checkResult = $this->checkPassword($password, $checkPwd);
            if ($checkResult) return $checkResult;
            // 에러메세지 없으면 비번수정해야 한다.
            $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $memberResult = $this->memberInfoRepository->update($updateData, ['user_id' => Context::getUserId()]);
        if ($memberResult < 1) return '수정한 정보가 없습니다.';
        return '';
    }

    public function changePassword($param): string
    {
        $password = ArrayUtil::getVal('password', $param);
        $checkPwd = ArrayUtil::getVal('check_pwd', $param);
        // 비번 체크하여 에러메세지 있으면 리턴한다.
        $checkResult = $this->checkPassword($password, $checkPwd);
        if ($checkResult) return $checkResult;

        $memberResult = $this->memberInfoRepository->update(['password' => $password], ['user_id' => Context::getUserId()]);
        if ($memberResult < 1) return '비밀번호 수정 실패';
        return '';
    }

    private function checkPassword($password, $checkPwd): string
    {
        if (!$password) return '비밀번호를 입력해 주세요.';

        // 비번 정규식 체크
        if (preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/', $password) === false) {
            return '비밀번호는 8자 이상의 영어, 숫자, 특수문자 조합으로 설정해 주세요.';
        }

        // 두번 입력한 비번이 다를 경우 실패
        if ($password !== $checkPwd) {
            return '비밀번호가 정확하지 않습니다.';
        }
        return '';
    }
}