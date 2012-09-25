<?php

namespace Kunstmaan\kServer\Provider;

use Cilex\ServiceProviderInterface;
use Kunstmaan\kServer\Entity\Project;
use Symfony\Component\Console\Output\OutputInterface;
use RuntimeException;
use Cilex\Application;
use Kunstmaan\kServer\Provider\FileSystemProvider;


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
    function register(Application $app)
    {
        $app['projectconfig'] = $this;
        $this->app = $app;
    }

    /**
     * @param $projectname
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return \Kunstmaan\kServer\Entity\Project
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
     * @param $projectname
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return \Kunstmaan\kServer\Entity\Project
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
