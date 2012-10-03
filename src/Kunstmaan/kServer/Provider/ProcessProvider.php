<?php

namespace Kunstmaan\kServer\Provider;

use Cilex\ServiceProviderInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Output\OutputInterface;
use RuntimeException;
use Cilex\Application;

/**
 * ProcessProvider
 */
class ProcessProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given app.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
        $app['process'] = $this;
    }

    /**
     * @param string          $command The command
     * @param OutputInterface $output  The command output stream
     * @param bool            $silent  Be silent or not
     *
     * @return bool|string
     */
    public function executeCommand($command, OutputInterface $output, $silent = false)
    {
        $output->writeln("<comment>      $ " . $command . "</comment>");
        $process = new Process($command);
        $process->setTimeout(3600);
        $process->run();
        if (!$process->isSuccessful()) {
            if (!$silent) {
                $output->writeln("<error>      " . $process->getErrorOutput() . "</error>");
            }

            return false;
        }

        return $process->getOutput();
    }
}
