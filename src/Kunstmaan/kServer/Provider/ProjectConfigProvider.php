<?php

namespace Kunstmaan\kServer\Provider;

use Cilex\ServiceProviderInterface;
use Kunstmaan\kServer\Entity\Project;
use Symfony\Component\Console\Output\OutputInterface;
use RuntimeException;
use Cilex\Application;
use Kunstmaan\kServer\Provider\FileSystemProvider;


class ProjectConfigProvider  implements ServiceProviderInterface
{

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

    public function createNewProjectConfig($projectname, OutputInterface $output){
        /** @var $filesystem FileSystemProvider */
        $filesystem = $this->app['filesystem'];
        $projectpath = $filesystem->getProjectDirectory($projectname);
        $output->writeln("<comment>      > Creating new Project object named $projectname in $projectpath/config/project.yml</comment>");
        $project = new Project($projectname, $projectpath . '/config/project.yml');
        return $project;
    }

}
