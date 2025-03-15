<?php

namespace App\Exception;

use Exception;
use Throwable;

class SystemException extends Exception
{
    protected array $data;

    public function __construct(string $message = "", int $code = 0, array $data = [], ?Throwable $previous = null)
    {
        $this->data = $data;
        parent::__construct($message, $code, $previous);
    }

    public function getData(): array
    {
        return $this->data;
    }
}
