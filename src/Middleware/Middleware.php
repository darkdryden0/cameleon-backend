<?php

namespace App\Middleware;

use App\Exception\ApiException;
use App\Exception\InvalidPramsException;
use App\Service\CsrfToken;
use App\Service\JwtService;
use App\Utils\Env;
use App\Utils\ResponseUtil;
use Exception;
use PDOException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ReverseContainer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class Middleware
{
    private array $allowPath = [
        // 헬스체크
        '/',
        '/api',
        '/api/health',
        // 앱 인증 url
        '/api/application/auth',
        '/api/application/redirect',
        // 토큰인증
        '/api/application/valid_token',
        '/api/application/context',
        // 회원 관련
        '/api/check/login',
        '/api/register/member'
    ];

    private array $intraPath = [
    ];

    private JwtService $jwtService;
    private CsrfToken $csrfToken;

    public function __construct(
        ReverseContainer $reverseContainer,
        LoggerInterface $appLogger,
        JwtService $jwtService,
        CsrfToken $csrfToken
    )
    {
        Context::setContainer($reverseContainer);
        Context::setLogger($appLogger);
        $this->jwtService = $jwtService;
        $this->csrfToken = $csrfToken;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $simplexiIp = $request->headers->get('x-simplexi0');
        if (is_string($simplexiIp) && strlen($simplexiIp) > 0) {
            Context::setIp($simplexiIp);
        } else {
            Context::setIp($request->getClientIp());
        }
        $reqId = $request->headers->get('x-reqid');
        if (is_string($reqId) && strlen($reqId) > 0) {
            Context::setTraceId($reqId);
        } else {
            Context::setTraceId();
        }

        $queryToken = $request->query->get('token');
        $headerToken = $request->headers->get('token');
        $cookieToken = $request->cookies->get('token');

        $token = $queryToken ?? $headerToken ?? $cookieToken ?? '';

        # /api 로 시작하는 주소만 front-end 를 통해 프락시 됨
        # /api/intra 로 시작하는 주소는 고정 토큰을 가진 내부 api 임

        if (in_array($request->getPathInfo(), $this->intraPath)) { // 내부 api

            if (!$token) {
                throw new UnauthorizedHttpException('IntraToken', 'Not found token');
            }

            if ($token !== Env::get('INTERNAL_TOKEN')) {
                throw new UnauthorizedHttpException('IntraToken', 'Invalid token');
            }

        } else if (
            in_array($request->getPathInfo(), $this->allowPath) === false // 인증이 필요 없은 api - 공개용
            && str_starts_with($request->getPathInfo(), '/api/open') === false // 인증 필요없는 오픈 api - 공개용
        ) {

            Context::setJwtToken($token);

            if (!$token) {
                throw new UnauthorizedHttpException('Jwt', 'Not found token');
            }

            try {
                $tokenInfo = $this->jwtService->decodeJwt($token);
            } catch (Exception $exception) {
                throw new UnauthorizedHttpException('Jwt', 'Invalid token', $exception);
            }

            if ($tokenInfo['exp'] - time() < 0) {
                throw new UnauthorizedHttpException('Jwt', 'token time out');
            }

            $tokenInfo['info'] = json_decode(json_encode($tokenInfo['info']), true);
            Context::setMallId($tokenInfo['info']['mall_id']);
            Context::setUserId($tokenInfo['info']['user_id']);

            // csrf check
            /*if (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE'])) {
                $csrf = $request->headers->get('csrf');
                if ($csrf === null) {
                    throw new AccessDeniedHttpException('Not found csrf');
                }
                if ($this->csrfToken->checkCsrfToken($csrf) === false) {
                    throw new AccessDeniedHttpException('Invalid csrf');
                }
            }*/
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        $method = $request->getMethod();
        $path = $request->getPathInfo();

        if (in_array($path, ['/', '/api', '/health']) === false) {
            // todo access log
        }

        $event->setResponse($response);

    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $event->allowCustomResponseCode();

        $detailErrorData = [
            'class' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'data' => [],
            'previous' => $exception->getPrevious()
        ];

        if ($exception instanceof NotFoundHttpException) {
            $event->setResponse(ResponseUtil::build([
                'code' => Response::HTTP_NOT_FOUND,
                'message' => $exception->getMessage(),
                'data' => Env::isDev() ? $detailErrorData : [],
            ]));
            return;
        }

        if ($exception instanceof UnauthorizedHttpException) {
            $event->setResponse(ResponseUtil::build([
                'code' => Response::HTTP_UNAUTHORIZED,
                'message' => 'Unauthorized: ' . $exception->getMessage(),
                'data' => Env::isDev() ? $detailErrorData : [],
            ]));
            return;
        }

        if (
            $exception instanceof BadRequestHttpException
            || $exception instanceof InvalidPramsException
        ) {
            $event->setResponse(ResponseUtil::build([
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => $exception->getMessage(),
                'data' => Env::isDev() ? $detailErrorData : [],
            ]));
            return;
        }

        if ($exception instanceof AccessDeniedHttpException) {
            $event->setResponse(ResponseUtil::build([
                'code' => Response::HTTP_FORBIDDEN,
                'message' => 'AccessDenied: ' . $exception->getMessage(),
                'data' => Env::isDev() ? $detailErrorData : [],
            ]));
            return;
        }

        if ($exception instanceof PDOException) {
            Context::getLogger()->critical('DB ERROR', $detailErrorData);
            $event->setResponse(ResponseUtil::build([
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'DB ERROR',
                'data' => Env::isDev() ? $detailErrorData : [],
            ]));
            return;
        }

        if ($exception instanceof ApiException) {
            $detailErrorData['data'] = $exception->getData();
            Context::getLogger()->critical('API ERROR', $detailErrorData);
            $event->setResponse(ResponseUtil::build([
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'API ERROR',
                'data' => Env::isDev() ? $detailErrorData : [],
            ]));
            return;
        }

        // 캐치 되지 않은 에러는 앞으로 구제적으로 처리해 줘야 함
        Context::getLogger()->critical('NO CATCH ERROR', $detailErrorData);

        $event->setResponse(ResponseUtil::build([
            'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
            'message' => $exception->getMessage(),
            'data' => Env::isDev() ? $detailErrorData : [],
        ]));
    }
}