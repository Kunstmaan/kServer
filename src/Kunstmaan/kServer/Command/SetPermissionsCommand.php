<?php
namespace Kunstmaan\kServer\Command;

use Symfony\Component\Console\Input\InputArgument;
use Kunstmaan\kServer\Provider\ProjectConfigProvider;
use Kunstmaan\kServer\Provider\FilesystemProvider;
use Kunstmaan\kServer\Skeleton\BaseSkeleton;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;

class SetPermissionsCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('permissions')
            ->setDescription('Set the permissions of a kServer project')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the project');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectname = $input->getArgument('name');
        /** @var $filesystem FileSystemProvider */
        $filesystem = $this->getService('filesystem');

        if(!$filesystem->projectExists($projectname)){
            throw new RuntimeException("The $projectname project does not exist.");
        }

        $output->writeln("<info> ---> Setting permissions on project $projectname</info>");

        /** @var $projectConfig ProjectConfigProvider */
        $projectConfig = $this->getService('projectconfig');
        $baseSkeleton = new BaseSkeleton();
        $project = $projectConfig->loadProjectConfig($projectname, $output);
        $baseSkeleton->permissions($this->getContainer(), $project, $output);
    }
}
