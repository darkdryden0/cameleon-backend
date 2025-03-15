<?php

namespace App\Service\Cafe24Api;

use App\Exception\ApiException;
use App\Exception\Cafe24ApiException;
use App\Middleware\Context;
use App\Utils\CurlUtil;
use App\Utils\Env;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;

abstract class Base
{
    protected Client $client;
    protected LoggerInterface $appLogger;

    protected string $mallId = '';

    protected string $accessToken;
    protected string $path;
    protected array $param;

    protected string $method;
    protected string $url = '';
    protected array $header;
    protected mixed $body;
    protected array $result = [];

    public function __construct(Client $client, LoggerInterface $appLogger)
    {
        $this->client = $client;
        $this->appLogger = $appLogger;
    }

    public function setUrl(): void
    {
        $this->url = sprintf("https://%s.cafe24api.com%s", $this->getMallId(), $this->path);
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setHeader(): void
    {
        $this->header = [
            'Content-Type' => 'application/json',
            'X-Cafe24-Api-Version' => Env::get('APP_VERSION'),
            'Authorization' => 'Bearer ' . $this->accessToken,
        ];
    }

    public function setBody(): void
    {
        $this->body = array_filter($this->param);
    }

    /**
     * @return void
     * @throws ApiException
     * @throws Cafe24ApiException
     */
    public function callWithTimeLog(): void
    {
        $startTime = microtime(true);

        $this->call();

        $endTime = microtime(true);

        $executionTime = $endTime - $startTime;
        $this->appLogger->info('V2X API 실행 시간', [
            'method' => $this->method,
            'url' => $this->url,
            'execution_time' => round($executionTime * 1000, 0),
        ]);

        if ($executionTime > 3) {
            $this->appLogger->warning('V2X API 실행 시간 3초 이상', [
                'method' => $this->method,
                'url' => $this->url,
                'execution_time' => round($executionTime * 1000, 0) ,
            ]);
        }
    }

    /**
     * @return void
     * @throws ApiException
     * @throws Cafe24ApiException
     */
    public function call(): void
    {
        try {

            if($this->method === 'POST') {
                $response = $this->client->post($this->url, defineGuzzleHttpOption([
                    'headers' => $this->header,
                    'body' => json_encode($this->body)
                ]));

            } elseif($this->method === 'POST_QUERY') {
                $response = $this->client->post($this->url, defineGuzzleHttpOption([
                    'headers' => $this->header,
                    'form_params' => $this->body
                ]));
            } elseif($this->method === 'PUT') {
                $response = $this->client->put($this->url, defineGuzzleHttpOption([
                    'headers' => $this->header,
                    'body' => json_encode($this->body)
                ]));
            } elseif($this->method === 'DELETE') {
                $response = $this->client->delete($this->url, defineGuzzleHttpOption([
                    'headers' => $this->header,
                    'body' => json_encode($this->body)
                ]));
            } else {
                $sQuery = CurlUtil::buildQuery([], $this->body);
                $getUrl = $this->url . "?" . $sQuery;
                $response = $this->client->get($getUrl, defineGuzzleHttpOption([
                    'headers' => $this->header
                ]));
            }

        } catch (RequestException $exception) {
            throw new Cafe24ApiException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception->hasResponse() ?
                    [
                        'method' => $this->method,
                        'url' => $this->url,
                        'header' => $this->header,
                        'body' => $this->body,
                        'response_status' => $exception->getResponse()->getStatusCode(),
                        'response_contents' => $exception->getResponse()->getBody()->getContents(),
                    ] :
                    []
            );
        } catch (GuzzleException $exception) {
            throw new ApiException(
                $exception->getMessage(),
                $exception->getCode(), [
                'method' => $this->method,
                'url' => $this->url,
                'header' => $this->header,
            ], $exception);
        }

        $this->result = [
            'code' => $response->getStatusCode(),
            'body' => json_decode($response->getBody()->getContents(), true),
        ];
    }

    public function setResult(): array
    {
        $this->appLogger->info(
            'Cafe24 V2X API Result',
            [
                'method' => $this->method,
                'url' => $this->url,
                'header' => $this->header,
                'body' => $this->body,
                'result' => $this->result,
            ]
        );

        return $this->result;
    }

    /**
     * @param $param
     * @param string $accessToken
     * @return array
     * @throws ApiException
     * @throws Cafe24ApiException
     */
    public function send($param, string $accessToken = ''): array
    {
        $this->accessToken = $accessToken;
        $this->param = $param;

        $this->setUrl();
        $this->setHeader();
        $this->setBody();

        try {
            $this->callWithTimeLog();
        } catch (ApiException $e) {

            //  429 에러(Too Many Request) 발생 시 2초 대기 후 retry
            if ($e->getCode() === 429) {
                sleep(2);
                $this->callWithTimeLog();
            } else {
                throw $e;
            }
        }

        return $this->setResult();

    }

    public function getMallId(): string
    {
        return strlen($this->mallId) > 0 ? $this->mallId : Context::getMallId();
    }
}