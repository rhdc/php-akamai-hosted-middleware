<?php
/**
 * This file is part of the RHDC Akamai middleware package.
 *
 * (c) Shawn Iwinski <siwinski@redhat.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */
namespace Rhdc\Akamai\Middleware\Test\Request;

use PHPUnit\Framework\TestCase;
use Rhdc\Akamai\Edge\Resolver\NativeResolver;
use Rhdc\Akamai\Middleware\Request\ResolverMiddleware;
use RingCentral\Psr7\Request;

class ResolverMiddlewareTest extends TestCase
{
    /** @var  NativeResolver */
    protected $resolver;

    protected function setUp()
    {
        // @todo Change to `NativeResolver::class` when PHP 5.3 and 5.4
        //       support is dropped
        $this->resolver = $this->createMock('Rhdc\\Akamai\\Edge\\Resolver\\NativeResolver');
    }

    public function testConstructResolver()
    {
        $resolverMiddleware = new ResolverMiddleware($this->resolver);
        $this->assertSame($this->resolver, $resolverMiddleware->getResolver());
    }

    public function testConstructDefaultResolve()
    {
        $resolverMiddleware = new ResolverMiddleware($this->resolver);
        $this->assertSame(NativeResolver::RESOLVE_HOST, $resolverMiddleware->getResolve());
    }

    public function testConstructCustomResolve()
    {
        $resolverMiddleware = new ResolverMiddleware($this->resolver, NativeResolver::RESOLVE_IP_V6);
        $this->assertSame(NativeResolver::RESOLVE_IP_V6, $resolverMiddleware->getResolve());
    }

    public function testConstructDefaultStaging()
    {
        $resolverMiddleware = new ResolverMiddleware($this->resolver);
        $this->assertSame(false, $resolverMiddleware->getStaging());
    }

    public function testConstructCustomStaging()
    {
        $resolverMiddleware = new ResolverMiddleware($this->resolver, NativeResolver::RESOLVE_HOST, true);
        $this->assertSame(true, $resolverMiddleware->getStaging());
    }

    public function testSetResolver()
    {
        $resolverMiddleware = new ResolverMiddleware($this->resolver);
        $newResolver = clone $this->resolver;

        $resolverMiddleware->setResolver($newResolver);

        $this->assertSame($newResolver, $resolverMiddleware->getResolver());
    }

    public function testSetResolverFluentInterface()
    {
        $resolverMiddleware = new ResolverMiddleware($this->resolver);
        $this->assertSame($resolverMiddleware, $resolverMiddleware->setResolver($this->resolver));
    }

    public function testSetResolve()
    {
        $resolverMiddleware = new ResolverMiddleware($this->resolver);
        $resolverMiddleware->setResolve(NativeResolver::RESOLVE_IP_V6);

        $this->assertSame(NativeResolver::RESOLVE_IP_V6, $resolverMiddleware->getResolve());
    }

    public function testSetResolveFluentInterface()
    {
        $resolverMiddleware = new ResolverMiddleware($this->resolver);
        $this->assertSame($resolverMiddleware, $resolverMiddleware->setResolve(null));
    }

    public function testSetStaging()
    {
        $resolverMiddleware = new ResolverMiddleware($this->resolver);
        $resolverMiddleware->setStaging(true);

        $this->assertSame(true, $resolverMiddleware->getStaging());
    }

    public function testSetStagingFluentInterface()
    {
        $resolverMiddleware = new ResolverMiddleware($this->resolver);
        $this->assertSame($resolverMiddleware, $resolverMiddleware->setStaging(null));
    }

    public function testInvokeNoHost()
    {
        $originalRequest = new Request('GET', '/');
        $resolverMiddleware = new ResolverMiddleware($this->resolver);
        $newRequest = $resolverMiddleware($originalRequest);

        $this->assertSame($originalRequest, $newRequest);
    }

    public function testInvokeNotIsResolvableHost()
    {
        $this->resolver->method('isResolvableHost')
            ->willReturn(false);

        $originalRequest = new Request('GET', 'https://www.akamai.com');
        $resolverMiddleware = new ResolverMiddleware($this->resolver);
        $newRequest = $resolverMiddleware($originalRequest);

        $this->assertSame($originalRequest, $newRequest);
    }

    protected function createTestInvokeObjects($resolveArray = false)
    {
        $this->resolver->method('isResolvableHost')
            ->willReturn(true);
        $this->resolver->method('resolve')
            ->willReturn($resolveArray ? array('1.1.1.1', '2.2.2.2') : 'resolved.akamai.com');

        $originalRequest = new Request('GET', 'https://www.akamai.com');
        $resolverMiddleware = new ResolverMiddleware($this->resolver);
        $newRequest = $resolverMiddleware($originalRequest);

        return array($originalRequest, $newRequest);
    }

    public function testInvokeNewInstance()
    {
        list($originalRequest, $newRequest) = $this->createTestInvokeObjects();
        $this->assertNotSame($originalRequest, $newRequest);
    }

    public function testInvokeUriHost()
    {
        list($originalRequest, $newRequest) = $this->createTestInvokeObjects();
        $this->assertSame('resolved.akamai.com', $newRequest->getUri()->getHost());
    }

    public function testInvokeHostHeader()
    {
        list($originalRequest, $newRequest) = $this->createTestInvokeObjects();
        $this->assertSame('www.akamai.com', $newRequest->getHeaderLine('Host'));
    }

    public function testInvokeUriHostWithResolveArray()
    {
        list($originalRequest, $newRequest) = $this->createTestInvokeObjects(true);
        $this->assertSame('1.1.1.1', $newRequest->getUri()->getHost());
    }
}
