<?php
namespace Kunstmaan\kServer\Command;

use Symfony\Component\Console\Input\InputArgument;
use Kunstmaan\kServer\Skeleton\BaseSkeleton;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;

class NewProjectCommand extends AbstractCommand
{

    protected function configure()
    {
        $this
            ->setName('new')
            ->setDescription('Create a new kServer project')
            ->addArgument('name', InputArgument::OPTIONAL, 'The name of the project. All lowercase, no spaces or special characters. Keep it short, yet descriptive');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectname = $this->dialog->askFor('name', "Please enter the name of the project. All lowercase, no spaces or special characters. Keep it short, yet descriptive",$input, $output);

        // Check if the project exists, do use in creating a new one with the same name.
        if ($this->filesystem->projectExists($projectname)) {
            throw new RuntimeException("A project with name $projectname already exists!");
        }

        $output->writeln("<info> ---> Creating project $projectname</info>");
        $this->filesystem->createProjectDirectory($projectname, $output);
        $project = $this->projectConfig->createNewProjectConfig($projectname, $output);
        $this->skeleton->applySkeleton($project, new BaseSkeleton(), $output);
        $project->writeConfig($output);
    }
}