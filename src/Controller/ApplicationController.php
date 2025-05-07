<?php

namespace App\Controller;

use App\Middleware\Context;
use App\Service\ApplicationService;
use App\Service\CsrfToken;
use App\Service\JwtService;
use App\Service\MallService;
use App\Utils\ArrayUtil;
use App\Utils\Env;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApplicationController extends BaseController
{
    private ApplicationService $applicationService;
    private MallService $mallService;
    private JwtService $jwtService;
    private CsrfToken $csrfToken;

    public function __construct(
        RequestStack       $request,
        LoggerInterface    $appLogger,
        ApplicationService $applicationService,
        MallService        $mallService,
        JwtService         $jwtService,
        CsrfToken          $csrfToken
    )
    {
        parent::__construct($request, $appLogger);
        $this->applicationService = $applicationService;
        $this->mallService = $mallService;
        $this->jwtService = $jwtService;
        $this->csrfToken = $csrfToken;
    }

    #[Route('/api/application/auth', methods: 'GET')]
    public function auth(): RedirectResponse|Response
    {
        $param = $this->getQueryParams();

        $errMsg = $this->applicationService->authValid($param);
        if ($errMsg !== '') {
            Context::getLogger()->error('auth error', [
                'app_path' => '/api/application/auth',
                'client_id' => Env::get('APP_CLIENT_ID'),
                'param' => $param
            ]);
            return $this->response($errMsg, [], Response::HTTP_BAD_REQUEST);
        }

        return new RedirectResponse($this->applicationService->auth($param));
    }

    #[Route('/api/application/redirect', methods: 'GET')]
    public function appRedirect(): RedirectResponse|Response
    {
        $param = $this->getQueryParams();
        $errMsg = $this->applicationService->redirectValid($param);
        if ($errMsg !== '') {
            Context::getLogger()->error('redirect error', [
                'client_id' => Env::get('APP_CLIENT_ID'),
                'param' => $param
            ]);
            return $this->response($errMsg, [], Response::HTTP_BAD_REQUEST);
        }

        // state 정보 파싱
        $state = json_decode(base64_decode($param['state']), true);

        // 몰정보
        $mallInfo = [];
        $mallId = ArrayUtil::getVal('mall_id', $state);
        $mallInfo['mall_id'] = $mallId;
        // 컨텍스트 설정
        Context::setMallId($mallId);

        $tokenInfo = $this->applicationService->getTokenByCode(ArrayUtil::getVal('code', $param));
        $userId = $this->mallService->setMallInfo($tokenInfo);
        $mallInfo['user_id'] = $userId;
        Context::setUserId($userId);

        // 이미 가입하면 메인페이지에 들어가고 아니면 로그인페이지
        if ($userId) {
            $jwtToken = $this->jwtService->encodeJwt($mallInfo);
            $frontUrl = Env::get('APP_FRONT') . '/fusion/auth?jwt=' . $jwtToken;
        } else {
            $frontUrl = Env::get('APP_FRONT') . '/fusion/member/login?shop_key=cafe24|' . $mallId;
        }
        return new RedirectResponse($frontUrl);
    }

    #[Route('/api/application/context', methods: 'GET')]
    public function context(): Response
    {
        return $this->response('success', [
            'mall_id' => Context::getMallId(),
            'user_id' => Context::getUserId(),
            'token' => $this->jwtService->encodeJwt(['mall_id' => 'testid2023', 'user_id' => 'testid2023'])
        ]);
    }

    #[Route('/api/application/csrf', methods: 'GET')]
    public function getCsrfToken(): Response
    {
        return $this->response('success', ['token' => $this->csrfToken->getCsrfToken()]);
    }

    #[Route('/api/application/mall_info', methods: 'GET')]
    public function getMallInfo(): Response
    {
        $mallInfo = $this->mallService->getMallInfo();
        return $this->response('success', $mallInfo);
    }

    #[Route('/api/application/valid_token', methods: 'POST')]
    public function checkValidToken(): Response
    {
        // login 페이지에서 토큰이 유효한지 판단하는 소스
        $param = $this->getContentParams();
        $token = ArrayUtil::getVal('token', $param);
        if (!$token) return $this->response('토큰값이 빈값일수 없습니다.', [],Response::HTTP_UNAUTHORIZED);

        try {
            $tokenInfo = $this->jwtService->decodeJwt($token);
        } catch (Exception $exception) {
            return $this->response($exception->getMessage(), [], Response::HTTP_UNAUTHORIZED);
        }
        $exp = ArrayUtil::getVal('exp', $tokenInfo);
        if ($exp - time() < 0) {
            return $this->response('토큰 타임아웃.', [], Response::HTTP_UNAUTHORIZED);
        }
        $result = $this->applicationService->checkValidToken($tokenInfo);
        if($result) return $this->response('성공.');

        return $this->response('유효한 토큰값이 아닙니다.', [], Response::HTTP_UNAUTHORIZED);
    }
}