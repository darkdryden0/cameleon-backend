<?php

namespace App\Service\Cafe24Api;

use App\Utils\ArrayUtil;

class AppstoreOrdersCreate extends Base
{
    protected string $method = 'POST';
    protected string $path = '/api/v2/admin/appstore/orders';

    public function setResult(): array
    {
        return ArrayUtil::getVal(['body', 'order'], $this->result, []);
    }
}