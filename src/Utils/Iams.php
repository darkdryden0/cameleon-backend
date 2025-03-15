<?php

namespace App\Utils;

class Iams
{
    public static function sendAlarm($message): string
    {
        // 초기화
        $curl = curl_init();
        $appEnv = Env::get('APP_ENV') ?: 'dev'; // 기본값은 'dev'
        $appEnv = ($_SERVER['SERVER_NAME'] == 'localhost') ? "local" : $appEnv;

        [$apiKey, $alarmRuleNo] = explode(':', Env::get('ALARM_KEY'));

        // cURL 옵션 설정
        curl_setopt_array($curl, array(
            CURLOPT_URL            => "https://iams-api.hanpda.com/v1/send-alarm?api_key=" . $apiKey,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POSTFIELDS     => json_encode(array(
                "alarm_rule_no" => $alarmRuleNo,
                "message"       => "[" . $appEnv . "] : " . $message
            )),
            CURLOPT_HTTPHEADER     => array(
                "Content-Type: application/json"
            ),
        ));

        // 요청 실행 및 응답 받기
        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return $err . ' : ' . $response;
        } else {
            return 'success';
        }
    }
}