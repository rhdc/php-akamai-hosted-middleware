<?php

namespace Rhdc\Akamai\Hosted\Middleware;

use Symfony\Component\Process\Process;

class StagingHostResolver extends StagingHostResolverAbstract
{
    protected $process;

    protected $stagingHosts = [];

    /**
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     * @throws \RuntimeException
     */
    public function resolve($host)
    {
        if (!$this->isResolvableHost($host)) {
            return $host;
        }

        $host = $this->normalizeHost($host);

        if (!isset($this->stagingHosts[$host])) {
            if (!isset($this->process)) {
                $this->process = new Process(implode(' | ', [
                    'dig $AKAMAI_STAGING_HOST',
                    'grep CNAME',
                    'grep \'akamaiedge.net\'',
                    'tail -1',
                    'awk \'{print $5}\''
                ]));
            }

            $this->process->mustRun(null, array(
                'AKAMAI_STAGING_HOST' => $host,
            ));

            $edgeHost = trim($process->getOutput());
            if (empty($edgeHost)) {
                throw new \RuntimeException(sprintf(
                    'Akamai edge host for "%s" not found',
                    $host
                ));
            }

            $this->stagingHosts[$host] = str_replace(
                'akamaiedge.net',
                'akamaiedge-staging.net',
                $edgeHost
            );
        }

        return $this->stagingHosts[$host];
    }
}
