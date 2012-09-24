<?php
namespace Kunstmaan\kServer\Command;

use Symfony\Component\Console\Input\InputArgument;
use Kunstmaan\kServer\Skeleton\BaseSkeleton;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;
use Kunstmaan\kServer\Provider\SkeletonProvider;
use Kunstmaan\kServer\Provider\FileSystemProvider;
use Kunstmaan\kServer\Provider\ProjectConfigProvider;

class NewProjectCommand extends Command
{


    protected function configure()
    {
        $this
            ->setName('newproject')
            ->setDescription('Create a new kServer project')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the project. All lowercase, no spaces or special characters. Keep it short, yet descriptive');


    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $filesystem FileSystemProvider */
        $filesystem = $this->getService('filesystem');
        /** @var $skeleton SkeletonProvider */
        $skeleton = $this->getService('skeleton');
        /** @var $projectConfig ProjectConfigProvider */
        $projectConfig = $this->getService('projectconfig');

        $projectname = $input->getArgument('name');

        // Check if the project exists, do use in creating a new one with the same name.
        if ($filesystem->projectExists($projectname)){
            throw new RuntimeException("A project with name $projectname already exists!");
        }

        $output->writeln("<info> ---> Creating project $projectname</info>");
        $filesystem->createProjectDirectory($projectname, $output);
        $project = $projectConfig->createNewProjectConfig($projectname, $output);
        $skeleton->applySkeleton($project, new BaseSkeleton(), $output);
        $project->writeConfig($output);
    }
}