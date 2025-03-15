<?php

namespace App\Controller;

use App\Message\CreditUpdate;
use App\Middleware\Context;
use App\Service\MallService;
use App\Service\CreditService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class CreditController extends BaseController
{
    private MallService $mallService;
    private CreditService $creditService;

    public function __construct(
        RequestStack    $request,
        LoggerInterface $appLogger,
        MallService     $mallService,
        CreditService   $creditService
    )
    {
        $this->mallService = $mallService;
        $this->creditService = $creditService;
        parent::__construct($request, $appLogger);
    }

    #[Route('/api/credit/list', methods: 'GET')]
    public function creditHistoryList(): Response
    {
        $param = $this->getQueryParams();
        $result = $this->creditService->creditHistoryList($param);
        return $this->response('success', $result);
    }

    #[Route('/api/credit/purchase', methods: 'POST')]
    public function purchaseCredit(): Response
    {
        $accessToken = $this->mallService->getAccessToken();
        if (strlen($accessToken) == 0) {
            return $this->response('access_token이 없습니다.', [], Response::HTTP_BAD_REQUEST);
        }
        $param = $this->getContentParams();
        $confirmUrl = $this->creditService->purchaseCredit($param, $accessToken);
        if (strlen($confirmUrl) == 0) {
            return $this->response('결제 Url이 없습니다.', [], Response::HTTP_BAD_REQUEST);
        }
        return new RedirectResponse($confirmUrl);
    }

    #[Route('/api/credit/increase', methods: 'PUT')]
    public function increaseCreditData(MessageBusInterface $messageBus): Response
    {
        $param = $this->getContentParams();
        $creditUpdate = new CreditUpdate('increase', Context::getMallId(), $param);
        $messageBus->dispatch($creditUpdate);
        return $this->response('success', []);
    }

    #[Route('/api/credit/decrease', methods: 'PUT')]
    public function decreaseCreditData(MessageBusInterface $messageBus): Response
    {
        $param = $this->getContentParams();
        $creditUpdate = new CreditUpdate('decrease', Context::getMallId(), $param);
        $messageBus->dispatch($creditUpdate);
        return $this->response('success', []);
    }
}