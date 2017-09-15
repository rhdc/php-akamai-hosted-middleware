<?php

namespace Rhdc\Akamai\Hosted\Middleware\Test;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Rhdc\Akamai\Hosted\Middleware\StagingMiddleware;
use Rhdc\Akamai\Hosted\Middleware\StagingHostResolver;

class StagingMiddlewareTest extends TestCase
{
    const MOCK_HOST = 'www.akamai.com';
    const MOCK_STAGING_HOST = 'staging.akamai.com';

    protected function createStagingHostResoverMock($isResolvableHost = true)
    {
        // @todo Switch to `StagingHostResolver::class` when PHP 5.4 support is dropped
        $stagingHostResolverMock = $this->createMock('\\Rhdc\\Akamai\\Hosted\\Middleware\\StagingHostResolver');

        $stagingHostResolverMock
            ->method('resolve')
            ->willReturn(static::MOCK_STAGING_HOST);

        $stagingHostResolverMock
            ->method('isResolvableHost')
            ->willReturn($isResolvableHost);

        return $stagingHostResolverMock;
    }

    protected function createMiddleware($isResolvableHost = true)
    {
        $stagingHostResolverMock = $this->createStagingHostResoverMock($isResolvableHost);

        return new StagingMiddleware($stagingHostResolverMock);
    }

    public function testConstructorWithoutProvidedStagingHostResolver()
    {
        $middleware = new StagingMiddleware();

        $this->assertInstanceof(
            // @todo Switch to `StagingHostResolver::class` when PHP 5.4 support is dropped
            'Rhdc\\Akamai\\Hosted\\Middleware\\StagingHostResolver',
            $middleware->getStagingHostResolver()
        );
    }

    public function testConstructorWithProvidedStagingHostResolver()
    {
        $stagingHostResolverMock = $this->createStagingHostResoverMock();
        $middleware = new StagingMiddleware($stagingHostResolverMock);

        $this->assertEquals(
            spl_object_hash($stagingHostResolverMock),
            spl_object_hash($middleware->getStagingHostResolver())
        );
    }

    public function testRequestWithResolvableHost()
    {
        $originalRequest = new Request('GET', 'https://'.static::MOCK_HOST);
        $modifiedRequest = $this->createMiddleware()->processRequest($originalRequest);

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
            $modifiedRequest->hasHeader('Host'),
            'Modified request should have a "Host" header'
        );

        $this->assertEquals(
            static::MOCK_HOST,
            $modifiedRequest->getHeader('Host')[0],
            'Invalid modified request "Host" header'
        );

        $this->assertEquals(
            static::MOCK_STAGING_HOST,
            $modifiedRequest->getUri()->getHost(),
            'Invalid modified request URI host'
        );
    }

    public function testRequestWithNonResolvableHost()
    {
        $nonResolvableHost = 'non-resolvable.'.static::MOCK_HOST;
        $originalRequest = new Request('GET', 'https://'.$nonResolvableHost);
        $modifiedRequest = $this->createMiddleware(false)->processRequest($originalRequest);

        $this->assertEquals(
            spl_object_hash($originalRequest),
            spl_object_hash($modifiedRequest),
            'Modified request should be the same instance'
        );

        $this->assertTrue(
            $modifiedRequest->hasHeader('Host'),
            'Modified request should have a "Host" header'
        );

        $this->assertEquals(
            $nonResolvableHost,
            $modifiedRequest->getHeader('Host')[0],
            'Invalid modified request "Host" header'
        );

        $this->assertEquals(
            $nonResolvableHost,
            $modifiedRequest->getUri()->getHost(),
            'Invalid modified request URI host'
        );
    }

    protected function processResponse(array $headers = [])
    {
        $request = new Request('GET', 'https://www.akamai.com');
        $response = new Response(200, $headers);
        return (new StagingMiddleware())->processResponse($request, $response);
    }

    public function testValidResponse()
    {
        $response = $this->processResponse([
            StagingMiddleware::HEADER => 'ESSL'
        ]);

        $this->assertInstanceOf('GuzzleHttp\\Psr7\\Response', $response);
    }

    public function testValidResponseCaseInsensitive()
    {
        $response = $this->processResponse([
            strtolower(StagingMiddleware::HEADER) => 'eSsL'
        ]);

        $this->assertInstanceOf('GuzzleHttp\\Psr7\\Response', $response);

        $response = $this->processResponse([
            strtoupper(StagingMiddleware::HEADER) => 'EsSl'
        ]);

        $this->assertInstanceOf('GuzzleHttp\\Psr7\\Response', $response);
    }

    /**
     * @expectedException Rhdc\Akamai\Hosted\Middleware\Exception\StagingResponseVerificationException
     */
    public function testInvalidResponseWithNoStagingHeader()
    {
        $this->processResponse();
    }

    /**
     * @expectedException Rhdc\Akamai\Hosted\Middleware\Exception\StagingResponseVerificationException
     */
    public function testInvalidResponseWithInvalidStagingHeaderValue()
    {
        $this->processResponse([
            StagingMiddleware::HEADER => 'invalid'
        ]);
    }
}
