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

class BackupCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('backup')
            ->setDescription('Run backup on all or one projects')
            ->addArgument('project', InputArgument::OPTIONAL, 'If set, the task will only backup the project named');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $filesystem FileSystemProvider */
        $filesystem = $this->getService('filesystem');
        /** @var $projectConfig ProjectConfigProvider */
        $projectConfig = $this->getService('projectconfig');
        $onlyprojectname = $input->getArgument('project');
        foreach($filesystem->getProjects() as $project){
            $projectname = $project->getFilename();
            if (isset($onlyprojectname) && $projectname != $onlyprojectname){
                continue;
            }
            $output->writeln("<info> ---> Running preBackup on project $projectname</info>");
            $project = $projectConfig->loadProjectConfig($projectname, $output);
            foreach($project->getDependencies() as $skeletonName => $skeletonClass){
                $output->writeln("<comment>      > Running maintenance of the $skeletonName skeleton</comment>");
                $skeleton = new $skeletonClass;
                $skeleton->preBackup($this->getContainer(), $project, $output);
            }
            $filesystem->runTar($project, $output);
            foreach($project->getDependencies() as $skeletonName => $skeletonClass){
                $output->writeln("<comment>      > Running maintenance of the $skeletonName skeleton</comment>");
                $skeleton = new $skeletonClass;
                $skeleton->postBackup($this->getContainer(), $project, $output);
            }
        }
    }
}
