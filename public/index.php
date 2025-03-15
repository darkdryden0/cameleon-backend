<?php
use App\Kernel;

// 타임존 한국 서울로 설정함
date_default_timezone_set('Asia/Seoul');

// ROOT 디랙토리
if (!defined('_PATH_ROOT_')) {
    define('_PATH_ROOT_', dirname(__DIR__));
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH');
    exit;
}

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';
require_once dirname(__DIR__).'/functions.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
