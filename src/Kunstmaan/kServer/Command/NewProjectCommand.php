<?php
namespace Kunstmaan\kServer\Command;

use Kunstmaan\kServer\Helper\OutputUtil;

use Symfony\Component\Console\Input\InputArgument;
use Kunstmaan\kServer\Skeleton\BaseSkeleton;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;

/**
 * NewProjectCommand
 */
class NewProjectCommand extends AbstractCommand
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('new')
            ->setDescription('Create a new kServer project')
            ->addArgument('name', InputArgument::OPTIONAL, 'The name of the project. All lowercase, no spaces or special characters. Keep it short, yet descriptive')
            ->addOption("migrate", null, InputOption::OPTIONAL, 'If set, the script will apply itself to an existing folder');
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
        $projectname = $this->dialog->askFor('name', "Please enter the name of the project. All lowercase, no spaces or special characters. Keep it short, yet descriptive", $input);

        OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, ($input->getOption('migrate')?"Migrating":"Creating") . " project $projectname");

        // Check if the project exists, do use in creating a new one with the same name.
        if ($this->filesystem->projectExists($projectname) && !$input->getOption('migrate')) {
            throw new RuntimeException("A project with name $projectname already exists!");
        }
        if (!$this->filesystem->projectExists($projectname)) {
            $this->filesystem->createProjectDirectory($projectname, $output);
        }

        $project = $this->projectConfig->createNewProjectConfig($projectname, $output);
        $this->skeleton->applySkeleton($project, $this->skeleton->findSkeleton("base"), $output);
        $this->projectConfig->writeProjectConfig($project, $output);
    }
}
