<?php

namespace App\Tests\Support;

trait Router
{
    /**
     * @param string $route
     * @param array $parameters
     * @return string
     */
    public function generateUrl(string $route, array $parameters = []): string
    {
        return Kernel::$container
            ->get('router')
            ->generate($route, $parameters);
    }
}