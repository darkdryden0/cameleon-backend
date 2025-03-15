<?php

namespace App\Consumer;

use App\Medoo\MedooConnect;
use App\Message\CreditUpdate;
use App\Middleware\Context;
use App\Service\MallService;
use App\Service\CreditService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler]
class CreditUpdateConsumer
{
    private LoggerInterface $appLogger;
    private MallService $mallService;
    private CreditService $creditService;
    public function __construct(
        LoggerInterface $appLogger,
        MallService     $mallService,
        CreditService    $creditService
    )
    {
        $this->appLogger = $appLogger;
        $this->mallService = $mallService;
        $this->creditService = $creditService;
    }

    public function __invoke(CreditUpdate $creditUpdate): void
    {
        try {
            // 기존 db 연결 끊고 다시 연결 함
            MedooConnect::remove();
            // 전송한 값들 추출
            $operateType = $creditUpdate->getOperateType();
            $mallId = $creditUpdate->getMallId();
            $param = $creditUpdate->getParam();
            // Context 세팅
            Context::setMallId($mallId);
            Context::setLogger($this->appLogger);

            // 포인트테이터 처리
            $mallInfo = $this->mallService->getMallInfo();
            if ($operateType === 'increase') {
                $this->creditService->increaseCreditData($param, $mallInfo);
            } elseif ($operateType === 'decrease') {
                $this->creditService->decreaseCreditData($param, $mallInfo);
            }
        } catch (Throwable $throwable) {
            // todo
        }
    }
}