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
    protected static $pragmaHeaders = [
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
     * Returns array of Akamai debug pragma header values
     *
     * @return string[]
     */
    public static function pragmaHeaders()
    {
        return static::$pragmaHeaders;
    }

    /**
     * Returns a new modified request instance with Akamai debug pragma headers
     *
     * - If the original request does not already have a pragma header, it will
     *   be added with the Akamai debug values
     * - If the original request already has (a) pragma header(s), the Akamai
     *   debug values will be combined with the existing value(s)
     *
     * @param RequestInterface $request Original request
     *
     * @return RequestInterface New modified request instance with Akamai debug pragma headers
     * @uses RequestInterface::withAddedHeader()
     */
    public function processRequest(RequestInterface $request)
    {
        return $request->withAddedHeader('Pragma', static::$pragmaHeaders);
    }
}
