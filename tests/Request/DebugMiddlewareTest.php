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

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Rhdc\Akamai\Middleware\Request\DebugMiddleware;
use RingCentral\Psr7\Request;

class DebugMiddlewareTest extends TestCase
{
    /**
     * @dataProvider requestHeadersProvider
     */
    public function testMiddleware(array $headers)
    {
        $originalRequest = new Request('GET', 'https://akamai.com', $headers);

        $debugMiddleware = new DebugMiddleware();
        $modifiedRequest = $debugMiddleware($originalRequest);

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
