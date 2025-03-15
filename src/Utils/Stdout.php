<?php

namespace App\Utils;

use App\Middleware\Context;
use DateTime;
use DateTimeZone;

class Stdout
{
    public static function log(array $logData): void
    {
        $out = fopen('php://stdout', 'w');

        $now = new DateTime();

        $data = [];
        $data['log_time'] = $now->format("Y-m-d H:i:s");
        $data['index_name'] = Env::get('DATA_EYE_INDEX_NAME');
        $data['custom_log'] = 'cf049221c0bd8d370e90a7b2f14004c2';
        $data['service_name'] =  Env::get('DATA_EYE_SERVICE_NAME');
        $data['service_domain'] = $_SERVER['HTTP_HOST'];
        $data['hostname'] = gethostname();
        $data['request_id'] = Context::getTraceId();
        $data['log_type'] = 'APP';
        $data['data'] = $logData;

        fputs($out, json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL);
        fclose($out);
    }
}