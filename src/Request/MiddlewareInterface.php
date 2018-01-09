<?php
/**
 * This file is part of the RHDC Akamai middlware package.
 *
 * (c) Shawn Iwinski <siwinski@redhat.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */
namespace Rhdc\Akamai\Middleware\Request;

use Psr\Http\Message\RequestInterface;

interface MiddlewareInterface
{
    /**
     * Processes request and returns a new modified `RequestInterface` instance
     *
     * @param RequestInterface $request Original request
     *
     * @return RequestInterface New modified request instance
     */
    public function __invoke(RequestInterface $request);
}
