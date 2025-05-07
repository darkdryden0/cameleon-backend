<?php

namespace App\Monolog;

use App\Medoo\Repository\LogsRepository;
use App\Middleware\Context;
use App\Utils\Env;
use App\Utils\Iams;
use App\Utils\Stdout;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

class AppLogHandler extends AbstractProcessingHandler
{
    private LogsRepository $logsRepository;

    public function __construct(int|string|Level $level = Level::Debug, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->logsRepository = new LogsRepository();
    }

    protected function write(LogRecord $record): void
    {
        // 에러메세지 파일에 넣어줌
        $message = "no_title";
        if (strlen($record->message) > 0) {
            $message = $record->message;
        }

        $level = $record->level->getName();
        $level = strtoupper($level);

        $logData  =[
            'level'   => $level,
            'message'  => $message,
            'content'  => $record->context,
            'mall_id'  => Context::getMallId(),
            'user_id'  => Context::getUserId(),
            'ip'       => Context::getIp(),
        ];

        // DateEye 로그
        Stdout::log($logData);

        // 알림
        //$this->sendAlarm($level, $message, $logData);

        // 개발용 로그
        $this->dbLog($level, $message, $logData);
    }

    private function sendAlarm($level, $message, $logData): void
    {
        // 중요한 에러는 알림
        if (in_array($level, ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'])) {
            unset($logData['level']);
            unset($logData['message']);
            $logData['log_time'] = date('Y-m-d H:i:s');
            $logData['trace_id'] = Context::getTraceId();
            Iams::sendAlarm(sprintf("[%s] %s %s", $level, $message,  json_encode($logData, JSON_UNESCAPED_UNICODE)));
        }
    }

    private function dbLog($level, $message, $logData): void
    {
        // 개발 환경 로그
        if (Env::isProd()) {
            return;
        }

        $this->logsRepository->insert([
            'type' => $level,
            'message' => $message,
            'contents' => json_encode($logData, JSON_UNESCAPED_UNICODE),
            'created' => date('Y-m-d H:i:s'),
        ]);
    }
}