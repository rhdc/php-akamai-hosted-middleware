<?php

namespace Rhdc\Akamai\Hosted\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Rhdc\Akamai\Hosted\Middleware\Exception\StagingResponseVerificationException;

class StagingMiddleware implements RequestMiddlewareInterface, ResponseMiddlewareInterface
{
    const HEADER = 'X-Akamai-Staging';

    /** @var StagingHostResolverInterface */
    protected $stagingHostResolver;

    public function __construct(StagingHostResolverInterface $stagingHostResolver = null)
    {
        $this->setStagingHostResolver($stagingHostResolver);
    }

    public function setStagingHostResolver($stagingHostResolver)
    {
        $this->stagingHostResolver = empty($stagingHostResolver)
            ? new StagingHostResolver()
            : $stagingHostResolver;

        return $this;
    }

    public function getStagingHostResolver()
    {
        return $this->stagingHostResolver;
    }

    public function processRequest(RequestInterface $request)
    {
        $uri = $request->getUri();
        $uriHost = $uri->getHost();

        if (!$this->stagingHostResolver->isResolvableHost($uriHost)) {
            return $request;
        }

        $stagingHost = $this->stagingHostResolver->resolve($uriHost);

        return $request
            ->withHeader('Host', $uriHost)
            ->withUri(
                // Set URI host to Akamai staging host
                $uri->withHost($stagingHost),
                // Preserve `Host` header and do not overwrite from URI
                true
            );
    }

    /**
     * @throws StagingResponseVerificationException if response can not be verified that
     *     it came from Akamai staging env
     */
    public function processResponse(RequestInterface $request, ResponseInterface $response)
    {
        $requestHost = $request->getUri()->getHost();
        if (!$this->stagingHostResolver->isResolvableHost($requestHost)) {
            return $response;
        }

        if (!$response->hasHeader(static::HEADER)) {
            throw new StagingResponseVerificationException(sprintf(
                'Response does not contain the "%s" header',
                static::HEADER
            ));
        }

        $xAkamaiStaging = $response->getHeader(static::HEADER)[0];
        if (false === stripos($xAkamaiStaging, 'essl')) {
            throw new StagingResponseVerificationException(sprintf(
                '"%s" header does not contain string "ESSL" (actual value = "%s")',
                static::HEADER,
                $xAkamaiStaging
            ));
        }

        return $response;
    }
}
