<?php
/**
 * This file is part of the RHDC Akamai middleware package.
 *
 * (c) Shawn Iwinski <siwinski@redhat.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */
namespace Rhdc\Akamai\Middleware\Response\Test;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Rhdc\Akamai\Middleware\Response\AssertStagingMiddleware;

class AssertStagingMiddlewareTest extends TestCase
{
    /**
     * @dataProvider responseHeadersProvider
     */
    public function testPragmaHeader(array $headers, $expectException)
    {
        if ($expectException) {
            $this->expectException('Rhdc\Akamai\Middleware\Response\AssertStagingMiddlewareException');
        }

        $originalResponse = new Response(200, $headers);

        $assertStagingMiddleware = new AssertStagingMiddleware();
        $modifiedResponse = $assertStagingMiddleware($originalResponse);

        if (!$expectException) {
            $this->assertTrue(
                $originalResponse instanceof ResponseInterface,
                'Returned response class is not an instance of Psr\\Http\\Message\\ResponseInterface'
            );

            $this->assertEquals(
                spl_object_hash($originalResponse),
                spl_object_hash($modifiedResponse),
                'Returned response should be the same instance'
            );
        }
    }

    public function responseHeadersProvider()
    {
        return array(
            array(array(AssertStagingMiddleware::HEADER => 'eSsL'), false),
            array(array(AssertStagingMiddleware::HEADER => 'eSsL, other'), false),
            array(array(AssertStagingMiddleware::HEADER => array('eSsL', 'other')), false),
            array(array(AssertStagingMiddleware::HEADER => ''), true),
            array(array(AssertStagingMiddleware::HEADER => 'invalid'), true),
            array(array(AssertStagingMiddleware::HEADER => 'invalid, other'), true),
            array(array(AssertStagingMiddleware::HEADER => array('invalid', 'other')), true),
            array(array(), true),
        );
    }
}
