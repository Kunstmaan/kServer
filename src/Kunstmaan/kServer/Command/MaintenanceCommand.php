<?php
namespace Kunstmaan\kServer\Command;

use Symfony\Component\Console\Input\InputArgument;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;

class MaintenanceCommand extends kServerCommand
{

    protected function configure()
    {
        $this
            ->setName('maintenance')
            ->setDescription('Run maintenance on all projects');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projects = $this->filesystem->getProjects();
        foreach ($projects as $project) {
            $projectname = $project->getFilename();
            $output->writeln("<info> ---> Running maintenance on project $projectname</info>");
            $project = $this->projectConfig->loadProjectConfig($projectname, $output);
            foreach ($project->getDependencies() as $skeletonName => $skeletonClass) {
                $output->writeln("<comment>      > Running maintenance of the $skeletonName skeleton</comment>");
                $skeleton = new $skeletonClass;
                $skeleton->maintenance($this->getContainer(), $project, $output);
            }
        }
    }
}
