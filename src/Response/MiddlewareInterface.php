<?php
/**
 * This file is part of the RHDC Akamai middleware package.
 *
 * (c) Shawn Iwinski <siwinski@redhat.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */
namespace Rhdc\Akamai\Middleware\Response;

use Psr\Http\Message\ResponseInterface;

interface MiddlewareInterface
{
    /**
     * Processes response and returns a new modified `ResponseInterface` instance
     *
     * @param ResponseInterface $response Original response
     *
     * @return ResponseInterface New modified response instance
     */
    public function __invoke(ResponseInterface $response);
}
