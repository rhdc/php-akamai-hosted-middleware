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

class AssertStagingMiddleware implements MiddlewareInterface
{
    const HEADER = 'X-Akamai-Staging';

    public function __invoke(ResponseInterface $response)
    {
        if (!$response->hasHeader(static::HEADER)) {
            throw new AssertStagingMiddlewareException(sprintf(
                'Response does not contain the "%s" header',
                static::HEADER
            ));
        }

        $headerValue = $response->getHeader(static::HEADER);
        if (empty($headerValue) || (stripos(strtolower($headerValue[0]), 'essl') !== 0)) {
            throw new AssertStagingMiddlewareException(sprintf(
                '"%s" header does not start with string "ESSL" (actual value = "%s")',
                static::HEADER,
                // Provide full header value instead of just first value
                $response->getHeaderLine(static::HEADER)
            ));
        }

        return $response;
    }
}
