<?php

namespace App\Message;

class CreditUpdate
{
    private string $operateType;
    private string $mallId;
    private array $param;

    public function __construct(
        string $operateType,
        string $mallId,
        array $param
    )
    {
        $this->operateType = $operateType;
        $this->mallId = $mallId;
        $this->param = $param;
    }

    public function getOperateType(): string
    {
        return $this->operateType;
    }

    public function getMallId(): string
    {
        return $this->mallId;
    }

    public function getParam(): array
    {
        return $this->param;
    }
}