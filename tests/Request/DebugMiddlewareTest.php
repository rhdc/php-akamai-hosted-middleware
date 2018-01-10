<?php
/**
 * This file is part of the RHDC Akamai middleware package.
 *
 * (c) Shawn Iwinski <siwinski@redhat.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */
namespace Rhdc\Akamai\Middleware\Request\Test;

use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Rhdc\Akamai\Middleware\Request\DebugMiddleware;

class DebugMiddlewareTest extends TestCase
{
    /**
     * @dataProvider requestHeadersProvider
     */
    public function testPragmaHeader(array $headers)
    {
        $originalRequest = new Request('GET', 'https://akamai.com', $headers);

        $debugMiddleware = new DebugMiddleware();
        $modifiedRequest = $debugMiddleware($originalRequest);

        $this->assertTrue(
            $originalRequest instanceof RequestInterface,
            'Modified request class is not an instance of Psr\\Http\\Message\\RequestInterface'
        );

        $this->assertNotEquals(
            spl_object_hash($originalRequest),
            spl_object_hash($modifiedRequest),
            'Modified request should be a new instance'
        );

        $this->assertTrue(
            $modifiedRequest->hasHeader('Pragma'),
            'Modified request should have a "Pragma" header'
        );

        $this->assertEquals(
            array_merge(
                $originalRequest->getHeader('Pragma'),
                DebugMiddleware::pragmaHeaders()
            ),
            $modifiedRequest->getHeader('Pragma'),
            'Invalid modified request "Pragma" header'
        );
    }

    public function requestHeadersProvider()
    {
        return array(
            // Empty/non-existent pragma header
            array(array()),
            // Pre-existing pragma header
            array(array('Pragma' => 'pre-existing'))
        );
    }
}
