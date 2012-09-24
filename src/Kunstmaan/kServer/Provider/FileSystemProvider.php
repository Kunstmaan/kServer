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
        $this->app["process"]->executeCommand(array('mkdir', '-p', $projectDirectory), $output);
    }

    public function createProjectConfigDirectory(Project $project, OutputInterface $output){
        $projectDirectory = $this->getProjectDirectory($project->getName());
        $this->app["process"]->executeCommand(array('mkdir', '-p', $projectDirectory . "/config"), $output);
    }
}

/*
import os
from smllib.information import Information

def existsFile(filename):
        """
               check if a file exists (python / jython)
        """
        try:
        	return os.access(filename,os.F_OK)
	except AttributeError:
		""" assuming jython """
		import java.io.File
		return java.io.File(filename).isFile()

def getBaseProjectDir():
	projdir = getBaseInformation()["config.projectsdir"]
	if "config.projectsdirprefix" in getBaseInformation().keys():
		projdir = getBaseInformation()["config.projectsdirprefix"]+projdir
	getBaseInformation()["tmp.projectsdir"] = projdir
	return projdir

def getProjectDirs():
	"""
		returns array of valid projectdirs
		and are considered to be valid if a file named /conf/config.xml exists
		this array can be empty, but not null
	"""
	projdir = getBaseProjectDir()

	dirs = [projdir + x for x in os.listdir(projdir) if existsFile(projdir + x + "/conf/config.xml")]
	if len(dirs) == 0:
	    print("*WARNING* no projects found in %s" % projdir)
	return dirs

def getProjectDir(projName):
    """
        returns the projectdir of projName
        and is considered to be valid if a file named /conf/config.xml exists
        if valid it returns a string with the projectdir, else it returns null
    """
    projDir = getBaseProjectDir()
    if existsFile(projDir + projName + "/conf/config.xml"):
        return projDir + projName
    else:
        print("*WARNING* no project %s found in  %s" % (projName,projDir))
        return None

baseconfig = []
def getBaseInformation():
    if (len(baseconfig) == 0):
        globalConfig = Information(None)
	globalConfig.bindXML("./config.xml")
	baseconfig.append(globalConfig)
    return baseconfig[0]

def getProjects():
	"""
		returns a list of valid projects on this machine
		a project is considered to be valid if a file named /conf/config.xml exists
		in the projectdir
	"""
	projdir = getBaseProjectDir()

	#this is needed only for the first time...
	os.system("mkdir -p %s" % projdir)

	projdirs = os.listdir(projdir)
	return [x for x in projdirs if existsFile(projdir + "/" + x + "/conf/config.xml")]

*/