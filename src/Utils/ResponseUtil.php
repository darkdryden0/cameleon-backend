<?php

namespace App\Utils;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ResponseUtil
{
    public static function build(array $content = [], array $addHeader = []): Response
    {
        $headers =[
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, PATCH',
            'Access-Control-Allow-Credentials' => 'true'
        ];

        $headers = array_merge($headers, $addHeader);

        return new JsonResponse($content, Response::HTTP_OK, $headers);
    }
}
