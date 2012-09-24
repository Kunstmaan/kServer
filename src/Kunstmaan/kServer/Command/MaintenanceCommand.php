<?php
namespace Kunstmaan\kServer\Command;

use Symfony\Component\Console\Input\InputArgument;
use Kunstmaan\kServer\Provider\ProjectConfigProvider;
use Kunstmaan\kServer\Provider\FilesystemProvider;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;

class MaintenanceCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('maintenance')
            ->setDescription('Run maintenance on all projects');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $filesystem FileSystemProvider */
        $filesystem = $this->getService('filesystem');
        /** @var $projectConfig ProjectConfigProvider */
        $projectConfig = $this->getService('projectconfig');
        foreach($filesystem->getProjects() as $project){
            $projectname = $project->getFilename();
            $output->writeln("<info> ---> Running maintenance on project $projectname</info>");
            $project = $projectConfig->loadProjectConfig($projectname, $output);
            foreach($project->getDependencies() as $skeletonName => $skeletonClass){
                $output->writeln("<comment>      > Running maintenance of the $skeletonName skeleton</comment>");
                $skeleton = new $skeletonClass;
                $skeleton->maintenance($this->getContainer(), $project, $output);
            }
        }
    }
}
