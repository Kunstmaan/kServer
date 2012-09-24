<?php

namespace Kunstmaan\kServer\Provider;

use Cilex\ServiceProviderInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Output\OutputInterface;
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

    public function executeCommand($command, OutputInterface $output, $silent=false){
        $output->writeln("<comment>      $ " . $command . "</comment>");
        $process = new Process($command);
        $process->run();
        if (!$process->isSuccessful()) {
            if (!$silent){
                $output->writeln("<error>      " . $process->getErrorOutput() . "</error>");
            }
            return false;
        }
        return $process->getOutput();
    }
}
