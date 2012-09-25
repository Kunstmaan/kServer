<?php
namespace Kunstmaan\kServer\Command;

use Symfony\Component\Console\Input\InputArgument;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;
use Kunstmaan\kServer\Skeleton\SkeletonInterface;
use Symfony\Component\Finder\SplFileInfo;

class BackupCommand extends kServerCommand
{

    protected function configure()
    {
        $this
            ->setName('backup')
            ->setDescription('Run backup on all or one projects')
            ->addArgument('project', InputArgument::OPTIONAL, 'If set, the task will only backup the project named')
            ->addOption("quick", null, InputArgument::OPTIONAL, 'If set, no tar.gz file will be created, only the preBackup and postBackup hooks will be executed.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->prepareProviders();

        $onlyprojectname = $input->getArgument('project');

        // Loop over all the projects to run the backup
        $projects = $this->filesystem->getProjects();

        /** @var $projectFile SplFileInfo */
        foreach ($projects as $projectFile) {

            // Check if the user wants to run the backup of only one project
            $projectname = $projectFile->getFilename();
            if (isset($onlyprojectname) && $projectname != $onlyprojectname) {
                continue;
            }

            $output->writeln("<info> ---> Running backup on project $projectname</info>");
            $project = $this->projectConfig->loadProjectConfig($projectname, $output);

            // Run the preBackup hook for all dependencies
            foreach ($project->getDependencies() as $skeletonName => $skeletonClass) {
                $output->writeln("<comment>      > Running preBackup of the $skeletonName skeleton</comment>");
                /** @var $skeleton SkeletonInterface */
                $skeleton = new $skeletonClass;
                $skeleton->preBackup($this->getContainer(), $project, $output);
            }

            if (!$input->getOption('quick')) {
                // Create the tar.gz file of the project directory
                $this->filesystem->runTar($project, $output);
            }

            // Run the postBackup hook for all dependencies
            foreach ($project->getDependencies() as $skeletonName => $skeletonClass) {
                $output->writeln("<comment>      > Running postBackup of the $skeletonName skeleton</comment>");
                /** @var $skeleton SkeletonInterface */
                $skeleton = new $skeletonClass;
                $skeleton->postBackup($this->getContainer(), $project, $output);
            }
        }
    }
}
