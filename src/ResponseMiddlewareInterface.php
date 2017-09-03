<?php

namespace Rhdc\Akamai\Hosted\Middleware;

use Psr\Http\Message\ResponseInterface;

interface ResponseMiddlewareInterface
{
    /**
     * Processes response and returns a new modified `ResponseInterface` instance
     *
     * @param ResponseInterface $response Original response
     *
     * @return ResponseInterface New modified response instance
     */
    public function processResponse(ResponseInterface $response);
}
