<?php

namespace Rhdc\Akamai\Hosted\Middleware;

interface StagingHostResolverInterface
{
    public function normalizeHost($host);

    public function normalizeHosts(array $hosts);

    public function setResolvableHosts($resolvableHosts);

    public function getResolvableHosts();

    public function isResolvableHost($host);

    public function resolve($host);
}
