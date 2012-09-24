<?php
namespace Kunstmaan\kServer\Provider;

use Cilex\ServiceProviderInterface;
use Kunstmaan\kServer\Entity\Project;
use Symfony\Component\Finder\Finder;
use Cilex\Application;
use Symfony\Component\Console\Output\OutputInterface;

class FileSystemProvider implements ServiceProviderInterface
{

    private $app;

    /**
     * Registers services on the given app.
     *
     * @param Application $app An Application instance
     */
    function register(Application $app)
    {
        $app['filesystem'] = $this;
        $this->app = $app;
    }

    /**
     * @return array
     */
    public function getProjects(){
        $finder = new Finder();
        $finder->directories()->sortByName()->in($this->app["config"]["projects"]["path"])->depth('== 0');
        return iterator_to_array($finder);
    }

    /**
     * @param $projectname
     * @return bool
     */
    public function projectExists($projectname){
        $finder = new Finder();
        $finder->directories()->sortByName()->in($this->app["config"]["projects"]["path"])->depth('== 0')->name($projectname);
        return $finder->count() != 0;
    }

    public function getProjectDirectory($projectname){
        return $this->app["config"]["projects"]["path"] . '/' . $projectname;
    }

    public function createProjectDirectory($projectname, OutputInterface $output){
        $projectDirectory = $this->getProjectDirectory($projectname);
        $this->app["process"]->executeCommand('mkdir -p ' . $projectDirectory, $output);
    }

    public function createProjectConfigDirectory(Project $project, OutputInterface $output){
        $projectDirectory = $this->getProjectDirectory($project->getName());
        $this->app["process"]->executeCommand('mkdir -p '. $projectDirectory . '/config', $output);
    }
}