<?php
namespace Kunstmaan\kServer\Command;

use Symfony\Component\Console\Input\InputArgument;
use Kunstmaan\kServer\Skeleton\BaseSkeleton;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;

class ApplySkeletonCommand extends kServerCommand
{

    protected function configure()
    {
        $this
            ->setName('apply')
            ->setDescription('Apply a skeleton to a kServer project')
            ->addArgument('project', InputArgument::OPTIONAL, 'The name of the kServer project')
            ->addArgument('skeleton', InputArgument::OPTIONAL, 'The name of the skeleton');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->prepareProviders();

        $projectname = $this->askFor('project', "Please enter the name of the project",$input, $output);

        // Check if the project exists, do use in creating a new one with the same name.
        if (!$this->filesystem->projectExists($projectname)) {
            throw new RuntimeException("A project with name $projectname should already exists!");
        }

        $skeletonname = $this->askFor('skeleton', "Please enter the name of the skeleton",$input, $output);
        $theSkeleton = $this->skeleton->findSkeleton($skeletonname);

        $project = $this->projectConfig->loadProjectConfig($projectname, $output);
        $this->skeleton->applySkeleton($project, $theSkeleton, $output);
        $project->writeConfig($output);
    }

}
