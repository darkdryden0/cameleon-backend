<?php

// 타임존 한국 서울로 설정함
date_default_timezone_set('Asia/Seoul');

require_once __DIR__ . '/vendor/autoload.php';

$ENV_KEYS = [
    'APP_CLIENT_ID',
    'APP_CLIENT_NAME',
    'APP_ENV',
    'APP_FRONT',
    'APP_BACK_HOST',
    'APP_SCOPE',
    'APP_SECRET',
    'APP_SECRET_KEY',
    'APP_SERVICE_KEY',
    'APP_VERSION',
    'JWT_KEY',
    'LOG_DB_HOST',
    'LOG_DB_NAME',
    'LOG_DB_PASSWORD',
    'LOG_DB_USER',
    'MESSENGER_TRANSPORT_DSN',
    'RABBITMQ_HOST',
    'RABBITMQ_PASSWORD',
    'RABBITMQ_PORT',
    'RABBITMQ_USER',
    'REDIS_HOST',
    'REDIS_PASSWORD',
    'REDIS_PORT',
];

$envContent = '';
foreach ($ENV_KEYS as $KEY) {
    $envContent .= sprintf("%s='%s'\n", $KEY,  getenv($KEY));
}

echo "write .env\n";
echo $envContent;

file_put_contents('/var/www/html/.env', $envContent);