<?php

namespace Rhdc\Akamai\Hosted\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Rhdc\Akamai\Hosted\Middleware\Exception\StagingResponseVerificationException;
use Symfony\Component\Process\Process;

class StagingMiddleware implements RequestMiddlewareInterface, ResponseMiddlewareInterface
{
    const HEADER = 'X-Akamai-Staging';

    protected static $stagingHosts = [];
    protected $originatingHost;

    /**
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     * @throws \Exception
     */
    public static function stagingHost($host)
    {
        if (!isset(static::$stagingHosts[$host])) {
            $process = new Process(implode(' | ', [
                "dig '$host'",
                'grep CNAME',
                'grep \'akamaiedge.net\'',
                'tail -1',
                'awk \'{print $5}\''
            ]));
            $process->mustRun();

            $edgeHost = trim($process->getOutput());
            if (empty($host)) {
                throw new \Exception(sprintf(
                    'Akamai edge host for "%s" not found',
                    $host
                ));
            }

            static::$stagingHosts[$host] = str_replace(
                'akamaiedge.net',
                'akamaiedge-staging.net',
                $edgeHost
            );
        }

        return static::$stagingHosts[$host];
    }

    public static function stagingHosts()
    {
        return static::$stagingHosts;
    }

    public function processRequest(RequestInterface $request)
    {
        $uri = $request->getUri();
        $uriHost = $uri->getHost();

        if (empty($this->originatingHost)) {
            $this->originatingHost = $uriHost;
        } elseif ($uriHost != $this->originatingHost) {
            return $request;
        }

        return $request
            ->withHeader('Host', $uriHost)
            ->withUri(
                // Set URI host to Akamai staging host
                $uri->withHost(static::stagingHost($uriHost)),
                // Preserve `Host` header and do not overwrite from URI
                true
            );
    }

    /**
     * @throws StagingResponseVerificationException if response can not be verified that
     *     it came from Akamai staging env
     */
    public function processResponse(ResponseInterface $response)
    {
        try {
            if (!$response->hasHeader(static::HEADER)) {
                throw new \Exception(sprintf(
                    'Response does not contain the "%s" header',
                    static::HEADER
                ));
            }

            $xAkamaiStaging = $response->getHeader(static::HEADER)[0];
            if (false === stripos($xAkamaiStaging, 'essl')) {
                throw new \Exception(sprintf(
                    '"%s" header does not contain string "ESSL" (actual value = "%s")',
                    static::HEADER,
                    $xAkamaiStaging
                ));
            }
        } catch (\Exception $e) {
            throw new StagingResponseVerificationException(sprintf(
                'Akamai response could not be verified that it came from staging env: %s',
                $e->getMessage()
            ));
        }

        return $response;
    }
}
