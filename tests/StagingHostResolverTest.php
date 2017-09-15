<?php

namespace Rhdc\Akamai\Hosted\Middleware\Test;

use PHPUnit\Framework\TestCase;
use Rhdc\Akamai\Hosted\Middleware\StagingHostResolver;

class StagingHostResolverTest extends TestCase
{
    /**
     * @dataProvider normalizeHostProvider
     */
    public function testNormalizeHost($rawHost, $expectedHost)
    {
        $stagingHostResolver = new StagingHostResolver();
        $actualHost = $stagingHostResolver->normalizeHost($rawHost);

        $this->assertEquals($expectedHost, $actualHost);
    }

    public function normalizeHostProvider()
    {
        return [
            ['www.akamai.com', 'www.akamai.com'],
            ['WwW.AkAmAi.CoM', 'www.akamai.com'],
            [' www.akamai.com ', 'www.akamai.com'],
            [PHP_EOL.'www.akamai.com'.PHP_EOL, 'www.akamai.com'],
            ["\twww.akamai.com\t", 'www.akamai.com'],
            [" \t".PHP_EOL, ''],
        ];
    }

    public function testNormalizeHosts()
    {
        $rawHosts = array_map(function ($normalizeHostProviderItem) {
            return $normalizeHostProviderItem[0];
        }, $this->normalizeHostProvider());

        $expectedNormalizedHosts = array_filter(array_map(function ($normalizeHostProviderItem) {
            return $normalizeHostProviderItem[1];
        }, $this->normalizeHostProvider()));

        $stagingHostResolver = new StagingHostResolver();
        $actualNormalizedHosts = $stagingHostResolver->normalizeHosts($rawHosts);

        $this->assertEquals($expectedNormalizedHosts, $actualNormalizedHosts);
    }

    /**
     * @dataProvider resolvableHostsProvider
     */
    public function testGetSetResolvableHosts($resolvableHosts)
    {
        $stagingHostResolver = new StagingHostResolver();

        // Assert beginning state
        $beginningState = $stagingHostResolver->getResolvableHosts();
        $this->assertTrue(is_array($beginningState));
        $this->assertEmpty($beginningState);

        $stagingHostResolver->setResolvableHosts($resolvableHosts);
        $newStateExpected = $stagingHostResolver->normalizeHosts((array) $resolvableHosts);
        $newStateActual = $stagingHostResolver->getResolvableHosts();
        $this->assertTrue(is_array($newStateActual));
        $this->assertEquals($newStateExpected, $newStateActual);
    }

    public function resolvableHostsProvider()
    {
        $normalizeHostsArray = array_map(function ($normalizeHostProviderItem) {
            return $normalizeHostProviderItem[0];
        }, $this->normalizeHostProvider());

        return [
            // Array with items requiring normalization
            [$normalizeHostsArray],
            // String
            ['www.akamai.com'],
        ];
    }

    /**
     * @dataProvider isResolvableHostProvider
     */
    public function testIsResolvableHost($resolvableHosts, $host, $expectedResult)
    {
        $stagingHostResolver = new StagingHostResolver();
        $stagingHostResolver->setResolvableHosts($resolvableHosts);

        $this->assertSame($expectedResult, $stagingHostResolver->isResolvableHost($host));
    }

    public function isResolvableHostProvider()
    {
        $resolvableHosts = [
            'akamai.com',
            'www.akamai.com',
        ];

        return [
            [$resolvableHosts, 'akamai.com', true],
            [$resolvableHosts, 'www.akamai.com', true],
            [$resolvableHosts, 'non-resolvable.akamai.com', false],
            [[], 'akamai.com', true],
            [[], 'www.akamai.com', true],
            [[], 'non-resolvable.akamai.com', true],
        ];
    }
}
