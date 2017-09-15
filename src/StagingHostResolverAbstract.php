<?php

namespace Rhdc\Akamai\Hosted\Middleware;

abstract class StagingHostResolverAbstract implements StagingHostResolverInterface
{
    /** @var string[] */
    protected $resolvableHosts = [];

    public function normalizeHost($host)
    {
        return strtolower(trim($host));
    }

    public function normalizeHosts(array $hosts)
    {
        return array_filter(array_map([$this, 'normalizeHost'], $hosts));
    }

    public function setResolvableHosts($resolvableHosts)
    {
        $this->resolvableHosts = $this->normalizeHosts((array) $resolvableHosts);

        return $this;
    }

    public function getResolvableHosts()
    {
        return $this->resolvableHosts;
    }

    public function isResolvableHost($host)
    {
        return
            empty($this->resolvableHosts)
            || in_array($this->normalizeHost($host), $this->resolvableHosts);
    }
}
