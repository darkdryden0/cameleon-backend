<?php

namespace App\Controller;

use App\Service\MemberService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MemberController extends BaseController
{
    private MemberService $memberService;
    public function __construct(
        RequestStack    $request,
        LoggerInterface $appLogger,
        MemberService   $memberService,
    )
    {
        parent::__construct($request, $appLogger);
        $this->memberService = $memberService;
    }

    #[Route('/api/register/member', methods: 'POST')]
    public function registerMember(): Response
    {
        $param = $this->getContentParams();
        // 이메일 사용가능한지 확인
        $emailFlag = $this->memberService->checkEmail($param);
        if (!$emailFlag) return $this->response('이미 가입한 이메일 입니다.', [], Response::HTTP_BAD_REQUEST);

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
        if (!$result) return $this->response('회원 등록 실패.', [], Response::HTTP_BAD_REQUEST);

        return $this->response('success', []);
    }
}