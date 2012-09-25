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
     * @param string $argumentname
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @throws RuntimeException
     */
    protected function askForProjectName($argumentname, InputInterface $input, OutputInterface $output){
        $projectname = $input->getArgument($argumentname);
        if (is_null($projectname)){
            $dialog = $this->getHelperSet()->get('dialog');
            $projectname = $dialog->ask($output, '<question>Please enter the name of the project. All lowercase, no spaces or special characters. Keep it short, yet descriptive: </question>');
        }
        if (is_null($projectname)){
            throw new RuntimeException("A projectname is required, what am I, psychic?");
        }
        return $projectname;
    }
}
