<?php

namespace App\Service;

use App\Medoo\Repository\CreditHistoryRepository;
use App\Medoo\Repository\MemberInfoRepository;
use App\Medoo\Repository\PaymentHistoryRepository;
use App\Middleware\Context;
use App\Service\Cafe24Api\AppstoreOrdersCreate;
use App\Utils\ArrayUtil;
use App\Utils\Env;
use Psr\Log\LoggerInterface;

class CreditService
{
    protected LoggerInterface $appLogger;
    private MemberInfoRepository $memberInfoRepository;
    private CreditHistoryRepository $creditHistoryRepository;
    private PaymentHistoryRepository $paymentHistoryRepository;
    private AppstoreOrdersCreate $appstoreOrdersCreate;

    public function __construct(
        LoggerInterface          $appLogger,
        MemberInfoRepository     $memberInfoRepository,
        CreditHistoryRepository  $creditHistoryRepository,
        PaymentHistoryRepository $paymentHistoryRepository,
        AppstoreOrdersCreate     $appstoreOrdersCreate,
    )
    {
        $this->appLogger = $appLogger;
        $this->memberInfoRepository = $memberInfoRepository;
        $this->creditHistoryRepository = $creditHistoryRepository;
        $this->paymentHistoryRepository = $paymentHistoryRepository;
        $this->appstoreOrdersCreate = $appstoreOrdersCreate;
    }

    public function creditHistoryList($param): array
    {
        $where = [
            'user_id' => Context::getUserId(),
        ];
        // 배열
        $orderField = ArrayUtil::getVal('order', $param);
        $orderBy = null;
        if ($orderField) {
            $orderBy = [
                $orderField => ArrayUtil::getVal('order_type', $param)
            ];
        }
        // 페이징
        $page = ArrayUtil::getVal('page', $param);
        $perPage = ArrayUtil::getVal('limit', $param);
        $limit = null;
        if ($page > 0 && $perPage > 0) {
            $limit = [$perPage * ($page - 1), $perPage];
        }
        return $this->creditHistoryRepository->findBy($where, $orderBy, $limit);
    }

    public function  purchaseCredit($param, $accessToken): string
    {
        $type = ArrayUtil::getVal('type', $param);
        if ($type === 'Basic') {
            $price = 100;
        } elseif ($type === 'Plus') {
            $price = 200;
        } elseif ($type === 'Pro') {
            $price = 300;
        } else {
            // 만약 타입이 없거나 이상한 값이면 결제 진행안한다.
            return '';
        }
        $apiParam = [
            'request' => [
                'order_name' => 'Carmeleon ' . $type,
                'order_amount' => $price,
                'return_url' => Env::get('APP_BACK_HOST') . '/api/credit/increase',
            ]
        ];
        $apiResult = $this->appstoreOrdersCreate->send($apiParam, $accessToken);
        $confirmationUrl = ArrayUtil::getVal('confirmation_url', $apiResult);
        if ($confirmationUrl) {
            $insertData = [
                'user_id' => Context::getUserId(),
                'order_id' => ArrayUtil::getVal('order_id', $apiResult),
                'order_name' => $type,
                'order_amount' => ArrayUtil::getVal('order_amount', $apiResult),
                'payment_date' => date('Y-m-d H:i:s'),
                'payment_result' => 'await',
            ];
            $this->paymentHistoryRepository->insert($insertData);
            return $confirmationUrl;
        }
        return '';
    }

    public function increaseCreditData($param, $mallInfo): string
    {
        $userId = Context::getUserId();
        $oldCredit = ArrayUtil::getVal('credit', $mallInfo,0);
        $operateCredit = ArrayUtil::getVal('credit', $param,0);
        $newCredit  = $oldCredit + $operateCredit;
        // 히스토리 테이블에 넣을 데이터
        $insertHistory = [
            'user_id' => $userId,
            'before_credit' => $oldCredit,
            'after_credit' => $newCredit,
            'operate_credit' => $operateCredit,
            'operate_type' => 'increase',
            'operate_date' => date('Y-m-d H:i:s'),
            'memo' => ArrayUtil::getVal('memo', $param),
        ];

        // 데이터 처리
        $creditResult = $this->creditHistoryRepository->insert($insertHistory);
        // 처리결과 -1이면 실패 리턴
        if ($creditResult === -1) return 'credit_history 테이블 처리 실패';
        $mallResult = $this->memberInfoRepository->update(['credit' => $newCredit], ['user_id' => $userId]);
        if ($mallResult < 1) return 'member_info 테이블 처리 실패';
        return '';
    }

    public function decreaseCreditData($param, $mallInfo): bool
    {
        $userId = Context::getUserId();
        $oldCredit = ArrayUtil::getVal('credit', $mallInfo,0);
        $operateCredit = ArrayUtil::getVal('credit', $param,0);
        $newCredit = $oldCredit - $operateCredit;
        if ($newCredit < 0) {
            return '포인트 부족';
        }
        // 히스토리 테이블에 넣을 데이터
        $insertHistory = [
            'user_id' => $userId,
            'before_credit' => $oldCredit,
            'after_credit' => $newCredit,
            'operate_credit' => $operateCredit,
            'operate_type' => 'decrease',
            'operate_date' => date('Y-m-d H:i:s'),
            'memo' => ArrayUtil::getVal('memo', $param),
        ];

        // 데이터 처리
        $creditResult = $this->creditHistoryRepository->insert($insertHistory);
        // 처리결과 -1이면 실패 리턴
        if ($creditResult === -1) return 'credit_history 테이블 처리 실패';
        $mallResult = $this->memberInfoRepository->update(['credit' => $newCredit], ['user_id' => $userId]);
        if ($mallResult < 1) return 'member_info 테이블 처리 실패';
        return '';
    }
}