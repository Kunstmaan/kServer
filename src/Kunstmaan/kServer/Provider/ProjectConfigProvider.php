<?php

namespace Kunstmaan\kServer\Provider;

use Symfony\Component\Yaml\Dumper;

use Symfony\Component\Yaml\Yaml;

use Kunstmaan\kServer\Helper\OutputUtil;

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
        $configPath = $projectpath . '/current/config/project.yml';
        OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, "Creating new Project object named $projectname in $projectpath/current/config/project.yml");
        $project = new Project($projectname, $configPath);

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
        /* @var $filesystem FileSystemProvider */
        $filesystem = $this->app['filesystem'];
        $projectpath = $filesystem->getProjectDirectory($projectname);
        $configPath = $projectpath . '/current/config/project.yml';
        $project = new Project($projectname, $configPath);

        $skeletons = $this->app["config"]["skeletons"];

        OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "Loading the project config from " . $configPath);
        $config = Yaml::parse($configPath);
        $config = new \ArrayObject($config['kserver']);

        /* @var $skeletonProvider SkeletonProvider */
        $skeletonProvider = $this->app['skeleton'];
        foreach ($config["dependencies"] as $depname => $dep) {
            $dep = $skeletonProvider->findSkeleton($depname);
            $project->addDependency($dep);
            $dep->loadConfig($project, $config);
        }

        return $project;
    }

    /**
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     */
    public function writeProjectConfig($project, OutputInterface $output)
    {
        /* @var $filesystem FileSystemProvider */
        $filesystem = $this->app['filesystem'];
        $projectpath = $filesystem->getProjectDirectory($project->getName());
        $configPath = $projectpath . '/current/config/project.yml';
        OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "Writing the project config to " . $configPath);

        $config = new \ArrayObject();
        $config["dependencies"] = $project->getDependencies();
        /* @var $skeletonProvider SkeletonProvider */
        $skeletonProvider = $this->app['skeleton'];
        foreach ($project->getDependencies() as $skeletonname => $skeletonclass) {
            $skeleton = $skeletonProvider->findSkeleton($skeletonname);
            $skeleton->writeConfig($project, $config);
        }

        $dumper = new Dumper();
        $yaml = $dumper->dump(array("kserver" => $config->getArrayCopy()), 5);
        file_put_contents($configPath, $yaml);
    }

}
