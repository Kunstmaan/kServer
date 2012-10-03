<?php

namespace Kunstmaan\kServer\Provider;

use Cilex\ServiceProviderInterface;
use Kunstmaan\kServer\Entity\Project;
use Symfony\Component\Console\Output\OutputInterface;
use RuntimeException;
use Cilex\Application;
use Kunstmaan\kServer\Provider\FileSystemProvider;

/**
 * ProjectConfigProvider
 */
class ProjectConfigProvider implements ServiceProviderInterface
{

    /**
     * @var Application
     */
    private $app;

    /**
     * Registers services on the given app.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
        $app['projectconfig'] = $this;
        $this->app = $app;
    }

    /**
     * @param string          $projectname The project name
     * @param OutputInterface $output      The command output stream
     *
     * @return Project
     */
    public function createNewProjectConfig($projectname, OutputInterface $output)
    {
        /** @var $filesystem FileSystemProvider */
        $filesystem = $this->app['filesystem'];
        $projectpath = $filesystem->getProjectDirectory($projectname);
        $output->writeln("<comment>      > Creating new Project object named $projectname in $projectpath/config/project.yml</comment>");
        $project = new Project($projectname, $projectpath . '/config/project.yml');

        return $project;
    }

    /**
     * @param string          $projectname The project name
     * @param OutputInterface $output      The command output stream
     *
     * @return Project
     */
    public function loadProjectConfig($projectname, OutputInterface $output)
    {
        /** @var $filesystem FileSystemProvider */
        $filesystem = $this->app['filesystem'];
        $projectpath = $filesystem->getProjectDirectory($projectname);
        $project = new Project($projectname, $projectpath . '/config/project.yml');
        $project->loadConfig($this->app["config"]["skeletons"], $output);

        return $project;
    }

}
