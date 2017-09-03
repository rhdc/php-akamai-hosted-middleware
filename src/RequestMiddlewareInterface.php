<?php

namespace Rhdc\Akamai\Hosted\Middleware;

use Psr\Http\Message\RequestInterface;

interface RequestMiddlewareInterface
{
    /**
     * Processes request and returns a new modified `RequestInterface` instance
     *
     * @param RequestInterface $request Original request
     *
     * @return RequestInterface New modified request instance
     */
    public function processRequest(RequestInterface $request);
}
