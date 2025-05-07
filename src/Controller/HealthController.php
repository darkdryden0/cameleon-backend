<?php

namespace App\Controller;

use App\Middleware\Context;
use App\Service\ConnectTest;
use App\Utils\Env;
use App\Utils\Iams;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HealthController extends BaseController
{
    #[Route('/', methods: 'GET')]
    #[Route('/api', methods: 'GET')]
    public function index(): Response
    {
        return $this->response('success', [
            'service_name' => 'Cameleon'
        ]);
    }

    #[Route('/api/health', methods: 'GET')]
    public function health(ConnectTest $connectTest): Response
    {
        $result['APP_ENV'] = Env::get('APP_ENV');
        $result['APP_VERSION'] = Env::get('APP_VERSION');

        $params  =$this->getQueryParams();
        if (array_key_exists('log', $params) && $params['log'] == 'T') {
            $redisResult = $connectTest->testRedis();
            $mqResult = $connectTest->testMq();

            $result['redis_error'] = $redisResult;
            $result['mq_error'] = $mqResult;

            //$result['alarm_result'] = Iams::sendAlarm('health check ' . print_r($result, true));

            Context::getLogger()->info('health check', $result);
        }

        return $this->response('success', $result);
    }
}