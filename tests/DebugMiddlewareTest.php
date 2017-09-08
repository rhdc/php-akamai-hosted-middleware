<?php

namespace Rhdc\Akamai\Hosted\Middleware\Test;

use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Rhdc\Akamai\Hosted\Middleware\DebugMiddleware;

class DebugMiddlewareTest extends TestCase
{
    /**
     * @dataProvider requestHeadersProvider
     */
    public function testPragmaHeader(array $headers)
    {
        $originalRequest = new Request('GET', 'https://akamai.com', $headers);
        $modifiedRequest = (new DebugMiddleware())->processRequest($originalRequest);

        $this->assertNotEquals(
            spl_object_hash($originalRequest),
            spl_object_hash($modifiedRequest),
            'Modified request should be a new instance'
        );

        $this->assertEquals(
            get_class($originalRequest),
            get_class($modifiedRequest),
            'Modified request class does not match original request class'
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
        return [
            // Empty/non-existent pragma header
            [[]],
            // Pre-existing pragma header
            [['Pragma' => 'pre-existing']]
        ];
    }
}
