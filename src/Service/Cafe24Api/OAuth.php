<?php

namespace App\Service\Cafe24Api;

use App\Utils\ArrayUtil;
use App\Utils\Env;

class OAuth extends Base
{
    protected string $method = 'POST_QUERY';
    protected string $path = '/api/v2/oauth/token';

    public function setHeader(): void
    {
        $this->header = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Bearer ' . base64_encode(Env::get('APP_CLIENT_ID') . ':' . Env::get('APP_SECRET_KEY'))
        ];
    }

    public function setResult(): array
    {
        return ArrayUtil::getVal(['body'], parent::setResult());
    }
}