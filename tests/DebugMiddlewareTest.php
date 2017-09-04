<?php

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
        $originalHeader = $originalRequest->getHeader('Pragma');

        $debugMiddleware = new DebugMiddleware();

        $modifiedRequest = $debugMiddleware->processRequest($originalRequest);
        $modifiedHeader = $modifiedRequest->getHeader('Pragma');

        // Assert new instance
        $this->assertNotEquals(
            spl_object_hash($originalRequest),
            spl_object_hash($modifiedRequest)
        );

        // Assert same class
        $this->assertEquals(
            get_class($originalRequest),
            get_class($modifiedRequest)
        );

        // Assert pragma header exists
        $this->assertTrue($modifiedRequest->hasHeader('Pragma'));

        // Assert pragma header
        $this->assertEquals(
            array_merge($originalHeader, DebugMiddleware::pragmaHeaders()),
            $modifiedHeader
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
