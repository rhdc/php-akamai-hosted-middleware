<?php
/**
 * This file is part of the RHDC Akamai middleware package.
 *
 * (c) Shawn Iwinski <siwinski@redhat.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */
namespace Rhdc\Akamai\Middleware\Request;

use Psr\Http\Message\RequestInterface;

/**
 * @see https://community.akamai.com/community/web-performance/blog/2015/03/31/using-akamai-pragma-headers-to-investigate-or-troubleshoot-akamai-content-delivery
 */
class DebugMiddleware implements MiddlewareInterface
{
    /**
     * Akamai debug pragma header values
     *
     * @var string[]
     */
    protected static $pragmaHeaders = array(
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
    );

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
     * - If the original request already has a pragma header, the Akamai debug
     *   values will be combined with the existing value(s)
     *
     * @param RequestInterface $request Original request
     *
     * @return RequestInterface New modified request instance with Akamai debug pragma headers
     */
    public function __invoke(RequestInterface $request)
    {
        return $request->withAddedHeader('Pragma', static::$pragmaHeaders);
    }
}
