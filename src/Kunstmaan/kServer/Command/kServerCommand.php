<?php
namespace Kunstmaan\kServer\Command;


use Cilex\Command\Command;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Cilex\Application;
use Kunstmaan\kServer\Provider\FileSystemProvider;
use Kunstmaan\kServer\Provider\ProjectConfigProvider;
use Kunstmaan\kServer\Provider\SkeletonProvider;
use Kunstmaan\kServer\Provider\ProcessProvider;
use Kunstmaan\kServer\Provider\PermissionsProvider;
use Symfony\Component\Console\Helper\DialogHelper;

abstract class kServerCommand extends Command
{

    /**
     * @var FileSystemProvider
     */
    protected $filesystem;
    /**
     * @var ProjectConfigProvider
     */
    protected $projectConfig;
    /**
     * @var SkeletonProvider
     */
    protected $skeleton;
    /**
     * @var ProcessProvider
     */
    protected $process;
    /**
     * @var PermissionsProvider
     */
    protected $permission;

    protected function prepareProviders()
    {
        $this->filesystem = $this->getService('filesystem');
        $this->projectConfig = $this->getService('projectconfig');
        $this->skeleton = $this->getService('skeleton');
        $this->process = $this->getService('process');
        $this->permission = $this->getService('permission');
    }

    /**
     * @param $argumentname
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return mixed|string
     * @throws \RuntimeException
     */
    protected function askFor($argumentname, $message, InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument($argumentname);
        if (is_null($name)) {
            /** @var $dialog DialogHelper */
            $dialog = $this->getHelperSet()->get('dialog');
            $name = $dialog->ask($output, '<question>'.$message.': </question>');
        }
        if (is_null($name)) {
            throw new RuntimeException("A $argumentname is required, what am I, psychic?");
        }
        return $name;
    }
}
