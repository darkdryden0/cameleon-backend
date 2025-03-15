<?php

namespace App\Controller;

use App\Utils\ResponseUtil;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends AbstractController
{
    protected Request $request;
    protected LoggerInterface $appLogger;

    public function __construct(
        RequestStack       $request,
        LoggerInterface    $appLogger,
    )
    {
        $this->request = $request->getCurrentRequest();
        $this->appLogger = $appLogger;
    }

    protected function getQueryParams(): array
    {
        return $this->request->query->all();
    }

    protected function getContentParams(): array
    {
        $param = $this->request->getContent();
        $result = json_decode($param, true);

        return is_array($result) ? $result : [];
    }

    protected function response($message = "", $data = [], $code = 200, array $addHeader = []): Response
    {
        $content = [
            "code"      => $code,
            "message"   => $message,
            "data"      => $data
        ];

        return ResponseUtil::build($content, $addHeader);
    }
}