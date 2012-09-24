<?php

namespace Kunstmaan\kServer\Provider;

use Cilex\ServiceProviderInterface;
use RuntimeException;
use Symfony\Component\Process\ProcessBuilder;
use Cilex\Application;

class ProcessProvider  implements ServiceProviderInterface
{
    /**
     * Registers services on the given app.
     *
     * @param Application $app An Application instance
     */
    function register(Application $app)
    {
        $app['process'] = $this;
    }

    public function executeCommand($commandarray){
        $builder = new ProcessBuilder($commandarray);
        $process = $builder->getProcess();
        $process->run();
        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput());
        }
        return $process->getOutput();
    }
}
