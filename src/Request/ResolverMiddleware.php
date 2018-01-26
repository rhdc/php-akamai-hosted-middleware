<?php
/**
 * This file is part of the RHDC Akamai middleware package.
 *
 * (c) Shawn Iwinski <siwinski@redhat.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */
namespace Rhdc\Akamai\Middleware\Request;

use Psr\Http\Message\RequestInterface;
use Rhdc\Akamai\Edge\Resolver\ResolverInterface;

class ResolverMiddleware implements MiddlewareInterface
{
    /** @var ResolverInterface */
    protected $resolver;

    /** @var string */
    protected $resolve;

    /** @var bool */
    protected $staging;

    public function __construct(
        ResolverInterface $resolver,
        $resolve = ResolverInterface::RESOLVE_HOST,
        $staging = false
    ) {
        $this
            ->setResolver($resolver)
            ->setResolve($resolve)
            ->setStaging($staging);
    }

    public function setResolver(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
        return $this;
    }

    public function getResolver()
    {
        return $this->resolver;
    }

    public function setResolve($resolve)
    {
        $this->resolve = $resolve;
        return $this;
    }

    public function getResolve()
    {
        return $this->resolve;
    }

    public function setStaging($staging)
    {
        $this->staging = (bool) $staging;
        return $this;
    }

    public function getStaging()
    {
        return $this->staging;
    }

    public function __invoke(RequestInterface $request)
    {
        $uri = $request->getUri();
        $host = $uri->getHost();

        if (empty($host) || !$this->resolver->isResolvableHost($host)) {
            return $request;
        }

        $resolve = $this->resolver->resolve($host, $this->resolve, $this->staging);
        $resolveHost = is_array($resolve) ? reset($resolve) : $resolve;
        $resolveUri = $uri->withHost($resolveHost);

        // Set resolve URI and preserve original host header
        return $request->withUri($resolveUri, true);
    }
}
