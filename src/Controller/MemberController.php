<?php

namespace App\Controller;

use App\Service\JwtService;
use App\Service\MemberService;
use App\Utils\ArrayUtil;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MemberController extends BaseController
{
    private JwtService $jwtService;
    private MemberService $memberService;

    public function __construct(
        RequestStack    $request,
        LoggerInterface $appLogger,
        MemberService   $memberService,
        JwtService      $jwtService,
    )
    {
        parent::__construct($request, $appLogger);
        $this->memberService = $memberService;
        $this->jwtService = $jwtService;
    }

    #[Route('/api/register/member', methods: 'POST')]
    public function registerMember(): Response
    {
        $param = $this->getContentParams();
        // 아이디 사용가능한지 확인
        $userFlag = $this->memberService->checkUserId($param);
        if (!$userFlag) return $this->response('이미 가입한 아이디 입니다.', [], Response::HTTP_BAD_REQUEST);

        // 비번 유효성체크
        $pwdResult = $this->memberService->validPassword($param);
        if ($pwdResult) return $this->response($pwdResult, [], Response::HTTP_BAD_REQUEST);

        // 회원가입 성공했는지 확인
        $result = $this->memberService->registerMember($param);
        if (!$result) return $this->response('회원가입 실패.', [], Response::HTTP_BAD_REQUEST);

        return $this->response('success', []);
    }

    #[Route('/api/check/login', methods: 'POST')]
    public function checkLogin(): Response
    {
        $param = $this->getContentParams();
        $result = $this->memberService->checkLogin($param);
        if (!$result) return $this->response('아이디 또는 비밀번호를 확인해주세요.', [], Response::HTTP_BAD_REQUEST);
        $token = $this->jwtService->encodeJwt(['mall_id' => 'testid2023', 'user_id' => ArrayUtil::getVal('user_id', $param)]);

        return $this->response('success', ['token' => $token]);
    }

    #[Route('/api/member/info', methods: 'GET')]
    public function getMemberInfo(): Response
    {
        $mallInfo = $this->memberService->getMemberInfo();
        return $this->response('success', $mallInfo);
    }

    #[Route('/api/modify/member', methods: 'PUT')]
    public function modifyMember(): Response
    {
        $param = $this->getContentParams();
        $result = $this->memberService->modifyMember($param);
        if ($result) return $this->response($result, [], Response::HTTP_BAD_REQUEST);

        return $this->response('success', []);
    }

    #[Route('/api/change/password', methods: 'PUT')]
    public function changePassword(): Response
    {
        $param = $this->getContentParams();
        $result = $this->memberService->changePassword($param);
        if ($result) return $this->response($result, [], Response::HTTP_BAD_REQUEST);

        return $this->response('success', []);
    }
}