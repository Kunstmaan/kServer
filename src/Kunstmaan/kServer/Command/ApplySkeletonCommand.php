<?php
namespace Kunstmaan\kServer\Command;

use Symfony\Component\Console\Input\InputArgument;
use Kunstmaan\kServer\Skeleton\BaseSkeleton;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;

/**
 * ApplySkeletonCommand
 */
class ApplySkeletonCommand extends AbstractCommand
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('apply')
            ->setDescription('Apply a skeleton to a kServer project')
            ->addArgument('project', InputArgument::OPTIONAL, 'The name of the kServer project')
            ->addArgument('skeleton', InputArgument::OPTIONAL, 'The name of the skeleton');
    }

    /**
     * @param InputInterface  $input  The command inputstream
     * @param OutputInterface $output The command outputstream
     *
     * @return int|void
     *
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectname = $this->dialog->askFor('project', "Please enter the name of the project", $input, $output);

        // Check if the project exists, do use in creating a new one with the same name.
        if (!$this->filesystem->projectExists($projectname)) {
            throw new RuntimeException("A project with name $projectname should already exists!");
        }

        $skeletonname = $this->dialog->askFor('skeleton', "Please enter the name of the skeleton", $input, $output);
        $theSkeleton = $this->skeleton->findSkeleton($skeletonname);

        $project = $this->projectConfig->loadProjectConfig($projectname, $output);
        $this->skeleton->applySkeleton($project, $theSkeleton, $output);
        $this->projectConfig->writeProjectConfig($project, $output);
    }

}
