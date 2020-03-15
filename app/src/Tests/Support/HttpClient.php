<?php

namespace App\Tests\Support;

use Symfony\Component\HttpKernel\Client;
use Symfony\Component\HttpKernel\HttpKernelBrowser;

trait HttpClient
{
    /**
     * @param array $server
     * @return Client
     */
    public function createClient(array $server = []): HttpKernelBrowser
    {
        $client = static::$container->get('test.client');
        $client->setServerParameters($server);

        return $client;
    }
}