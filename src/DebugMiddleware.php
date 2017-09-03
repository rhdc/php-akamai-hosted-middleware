<?php

namespace Rhdc\Akamai\Hosted\Middleware;

use Psr\Http\Message\RequestInterface;

class DebugMiddleware implements RequestMiddlewareInterface
{
    /**
     * Akamai debug pragma header values
     *
     * @var string[]
     */
    const PRAGMA_HEADERS = [
        'akamai-x-cache-on',
        'akamai-x-cache-remote-on',
        'akamai-x-check-cacheable',
        'akamai-x-feo-trace',
        'akamai-x-get-cache-key',
        'akamai-x-get-client-ip',
        'akamai-x-get-extracted-values',
        'akamai-x-get-nonces',
        'akamai-x-get-request-id',
        'akamai-x-get-ssl-client-session-id',
        'akamai-x-get-true-cache-key',
        'akamai-x-serial-no',
    ];

    /**
     * Returns a new modified request instance with Akamai debug pragma headers
     *
     * @param RequestInterface $request Original request
     *
     * @return RequestInterface New modified request instance with Akamai debug pragma headers
     */
    public function processRequest(RequestInterface $request)
    {
        return $request->withAddedHeader('Pragma', static::PRAGMA_HEADERS);
    }
}
