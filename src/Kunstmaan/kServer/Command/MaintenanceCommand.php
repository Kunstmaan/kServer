<?php
namespace Kunstmaan\kServer\Command;

use Symfony\Component\Console\Input\InputArgument;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;
use Symfony\Component\Finder\SplFileInfo;
use Kunstmaan\kServer\Skeleton\AbstractSkeleton;

/**
 * MaintenanceCommand
 */
class MaintenanceCommand extends AbstractCommand
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('maintenance')
            ->setDescription('Run maintenance on all projects');
    }

    /**
     * @param InputInterface  $input  The command inputstream
     * @param OutputInterface $output The command outputstream
     *
     * @return int|void
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->prepareProviders();

        $projects = $this->filesystem->getProjects();
        /** @var $projectFile SplFileInfo */
        foreach ($projects as $projectFile) {
            $projectname = $projectFile->getFilename();
            $output->writeln("<info> ---> Running maintenance on project $projectname</info>");
            $project = $this->projectConfig->loadProjectConfig($projectname, $output);
            foreach ($project->getDependencies() as $skeletonName => $skeletonClass) {
                $output->writeln("<comment>      > Running maintenance of the $skeletonName skeleton</comment>");
                /** @var $skeleton AbstractSkeleton */
                $skeleton = new $skeletonClass($this->getContainer());
                $skeleton->maintenance($this->getContainer(), $project, $output);
            }
        }
    }
}
