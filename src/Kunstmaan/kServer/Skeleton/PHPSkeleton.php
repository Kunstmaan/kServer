<?php
namespace Kunstmaan\kServer\Skeleton;

use Kunstmaan\kServer\Entity\PermissionDefinition;

use Kunstmaan\kServer\Entity\ApacheConfig;

use Cilex\Application;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Output\OutputInterface;
use Kunstmaan\kServer\Entity\Project;
use Kunstmaan\kServer\Provider\ProcessProvider;
use Kunstmaan\kServer\Provider\FileSystemProvider;

/**
 * ApacheSkeleton
 */
class PHPSkeleton extends AbstractSkeleton
{

    const NAME = "php";

    /**
     * @return string
     */
    public function getName()
    {
        return PHPSkeleton::NAME;
    }

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed
     */
    public function create(Application $app, Project $project, OutputInterface $output)
    {
        /** @var $process ProcessProvider */
        $process = $app["process"];
        /** @var $filesystem FileSystemProvider */
        $filesystem = $app["filesystem"];

        $process->executeCommand("rsync -rltD -vh --exclude \"apache\" " . $this->getTemplateDir() . " " . $filesystem->getProjectConfigDirectory($project->getName()), $output);

        $process->executeCommand("rsync -rltD -vh " . $this->getTemplateDir() . "/apache/ " . $filesystem->getProjectConfigDirectory($project->getName()). "/apache/", $output);

    }

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed
     */
    public function maintenance(Application $app, Project $project, OutputInterface $output)
    {
        $apacheConf = $project->getConfiguration(ApacheSkeleton::NAME);

        $configRenderParams = array(
                "project" => $project,
                "projectDir" => $app["config"]["projects"]["path"] . "/" . $project->getName() . "/",
                "documentRoot" => $app["config"]["projects"]["path"] . "/" . $project->getName() . "/current/" . $apacheConf->getWebDir(),
                "projectPath" => $app["config"]["projects"]["path"]
                );

        $shared = $app['twig']->render($this->getConfigDir($app, $project) . "/php5-fpm.conf.twig", $configRenderParams);
        file_put_contents("/etc/php5/fpm/pool.d/" . $project->getName() . ".conf", $shared);
    }

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed
     */
    public function preBackup(Application $app, Project $project, OutputInterface $output)
    {
        // TODO: Implement preBackup() method.
    }

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed
     */
    public function postBackup(Application $app, Project $project, OutputInterface $output)
    {
        // TODO: Implement postBackup() method.
    }

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed
     */
    public function preRemove(Application $app, Project $project, OutputInterface $output)
    {
        // TODO: Implement preRemove() method.
    }

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed
     */
    public function postRemove(Application $app, Project $project, OutputInterface $output)
    {
        // TODO: Implement postRemove() method.
    }

    /**
     * @param Project      $project The project
     * @param \ArrayObject $config  The configuration array
     */
    public function writeConfig(Project $project, \ArrayObject $config)
    {

    }

    /**
     * @param Project      $project The project
     * @param \ArrayObject $config  The configuration array
     */
    public function loadConfig(Project $project, \ArrayObject $config)
    {

    }

    /**
     * @return string
     */
    private function getTemplateDir()
    {
        return __DIR__ . "/../../../../templates/php";
    }

    /**
     * @param Application $app     The application
     * @param Project     $project The project
     *
     * @return string
     */
    private function getConfigDir(Application $app, Project $project)
    {
        /** @var $filesystem FileSystemProvider */
        $filesystem = $app["filesystem"];

        return $filesystem->getProjectConfigDirectory($project->getName()). "/php";
    }

    /**
     * @param \Cilex\Application $app
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return string[]
     */
    public function dependsOn(Application $app, Project $project, OutputInterface $output)
    {
        return array(
            "base",
            "apache"
        );
    }
}
