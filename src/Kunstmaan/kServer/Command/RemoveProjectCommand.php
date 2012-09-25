<?php
namespace Kunstmaan\kServer\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;
use Kunstmaan\kServer\Skeleton\BaseSkeleton;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;

class RemoveProjectCommand extends kServerCommand
{

    protected function configure()
    {
        $this
            ->setName('remove')
            ->setDescription('Removes a kServer project')
            ->addArgument('name', InputArgument::OPTIONAL, 'The name of the project.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->prepareProviders();

        $projectname = $this->askForProjectName('name', $input, $output);

        // Check if the project exists, do use in creating a new one with the same name.
        if (!$this->filesystem->projectExists($projectname)) {
            throw new RuntimeException("A project with name $projectname does not exist!");
        }

        $dialog = $this->getHelperSet()->get('dialog');
        if (!$dialog->askConfirmation($output, '<question>Are you sure you want to remove ' . $projectname . '?</question>', false)) {
            return;
        }

        $output->writeln("<info> ---> Removing project $projectname</info>");

        $command = $this->getApplication()->find('backup');
        $arguments = array(
            'command' => 'backup',
            'project' => $projectname,
        );
        $input = new ArrayInput($arguments);
        $returnCode = $command->run($input, $output);

        if (is_null($returnCode)) {
            //$this->permission->killProcesses($projectname, $output);
        }

        $project = $this->projectConfig->loadProjectConfig($projectname, $output);

        // Run the preRemove hook for all dependencies
        foreach ($project->getDependencies() as $skeletonName => $skeletonClass) {
            $output->writeln("<comment>      > Running preRemove of the $skeletonName skeleton</comment>");
            $skeleton = new $skeletonClass;
            $skeleton->preRemove($this->getContainer(), $project, $output);
        }

        $this->filesystem->removeProjectDirectory($project, $output);

        // Run the postRemove hook for all dependencies
        foreach ($project->getDependencies() as $skeletonName => $skeletonClass) {
            $output->writeln("<comment>      > Running postRemove of the $skeletonName skeleton</comment>");
            $skeleton = new $skeletonClass;
            $skeleton->postRemove($this->getContainer(), $project, $output);
        }
    }
}