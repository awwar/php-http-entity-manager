<?php

namespace Awwar\PhpHttpEntityManager\Tests\Stubs;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class HttpClientStub implements HttpClientInterface
{
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        throw new \RuntimeException('Not implemented');
    }

    public function stream($responses, float $timeout = null): ResponseStreamInterface
    {
        throw new \RuntimeException('Not implemented');
    }
}
