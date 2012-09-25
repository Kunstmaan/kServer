<?php
namespace Kunstmaan\kServer\Provider;

use Cilex\ServiceProviderInterface;
use Kunstmaan\kServer\Entity\Project;
use Symfony\Component\Finder\Finder;
use Cilex\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Kunstmaan\kServer\Provider\ProcessProvider;

class FileSystemProvider implements ServiceProviderInterface
{

    /**
     * @var Application
     */
    private $app;

    /**
     * @var ProcessProvider
     */
    private $process;

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
    public function getProjects()
    {
        $finder = new Finder();
        $finder->directories()->sortByName()->in($this->app["config"]["projects"]["path"])->depth('== 0');
        return iterator_to_array($finder);
    }

    /**
     * @param $projectname
     * @return bool
     */
    public function projectExists($projectname)
    {
        $finder = new Finder();
        $finder->directories()->sortByName()->in($this->app["config"]["projects"]["path"])->depth('== 0')->name($projectname);
        return $finder->count() != 0;
    }

    /**
     * @param $projectname
     * @return string
     */
    public function getProjectDirectory($projectname)
    {
        return $this->app["config"]["projects"]["path"] . '/' . $projectname;
    }

    /**
     * @param $projectname
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function createProjectDirectory($projectname, OutputInterface $output)
    {
        $projectDirectory = $this->getProjectDirectory($projectname);
        if (is_null($this->process)){ $this->process = $this->app["process"]; }
        $this->process->executeCommand('mkdir -p ' . $projectDirectory, $output);
    }

    /**
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function createProjectConfigDirectory(Project $project, OutputInterface $output)
    {
        $projectDirectory = $this->getProjectDirectory($project->getName());
        if (is_null($this->process)){ $this->process = $this->app["process"]; }
        $this->process->executeCommand('mkdir -p ' . $projectDirectory . '/config', $output);
    }

    /**
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function createMySQLBackupDirectory(Project $project, OutputInterface $output)
    {
        $projectDirectory = $this->getProjectDirectory($project->getName());
        if (is_null($this->process)){ $this->process = $this->app["process"]; }
        $this->process->executeCommand('mkdir -p ' . $projectDirectory . '/backup', $output);
    }

    /**
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function runTar(Project $project, OutputInterface $output)
    {
        if (is_null($this->process)){ $this->process = $this->app["process"]; }
        $this->process->executeCommand('mkdir -p ' . $this->app["config"]["projects"]["backuppath"], $output);
        $projectDirectory = $this->getProjectDirectory($project->getName());
        $excluded = '';
        if (!is_null($project->getExcludedFromBackup())) {
            foreach ($project->getExcludedFromBackup() as $excl) {
                $excluded = $excluded . " --exclude='" . $excl . "'";
            }
        }
        $this->process->executeCommand('nice -n 19 tar --create --absolute-names ' . $excluded . ' --file ' . $this->app["config"]["projects"]["backuppath"] . '/' . $project->getName() . '.tar.gz --totals --gzip ' . $projectDirectory . '/ 2>&1', $output);
    }

    /**
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function removeProjectDirectory(Project $project, OutputInterface $output)
    {
        $projectDirectory = $this->getProjectDirectory($project->getName());
        if (is_null($this->process)){ $this->process = $this->app["process"]; }
        $this->process->executeCommand("rm -Rf " . $projectDirectory, $output);
    }
}