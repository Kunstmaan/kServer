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

    public function runTar(Project $project, OutputInterface $output){
        $this->app["process"]->executeCommand('mkdir -p ' . $this->app["config"]["projects"]["backuppath"], $output);
        $projectDirectory = $this->getProjectDirectory($project->getName());
        $excluded = '';
        if(!is_null($project->getExcludedFromBackup())){
            foreach($project->getExcludedFromBackup() as $excl){
                $excluded = $excluded . " --exclude='".$excl."'";
            }
        }
        $this->app["process"]->executeCommand('nice -n 19 tar --create --absolute-names '.$excluded.' --file '.$this->app["config"]["projects"]["backuppath"].'/'.$project->getName().'.tar.gz --totals --gzip '.$projectDirectory.'/ 2>&1', $output);
    }
}
/*
	CMD("mkdir -p %s" % destdir)
	excludeparam = ""
	for exclude in excludes:
	    n = exclude
	    if (exclude.startswith("/")):
	        n = project['project.dir'] + exclude
	    if (exclude.endswith("/")):
            n = n + '*'
	    excludeparam = excludeparam + "--exclude='" + n + "' "
	D("Tarring project to %s%s, excluding %s" % (destdir, project['project.name'], excludes))
	D("--> nice -n 19 tar --create --absolute-names %s --file %s%s.tar.gz --totals --gzip %s/ 2>&1" % (excludeparam, destdir, project['project.name'], project['project.dir']))
	tarstats = CMDGET("nice -n 19 tar --create --absolute-names %s --file %s%s.tar.gz --totals --gzip %s/ 2>&1" % (excludeparam, destdir, project['project.name'], project['project.dir']))
	D(" --> Success [ %s ]" % tarstats.rstrip())
	D(" --> to restore the project do: # tar xzPf %s%s.tar.gz" % (destdir, project['project.name']))

*/