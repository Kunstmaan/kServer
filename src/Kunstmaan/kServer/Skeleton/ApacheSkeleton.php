<?php
namespace Kunstmaan\kServer\Skeleton;

use Cilex\Application;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Output\OutputInterface;
use Kunstmaan\kServer\Entity\Project;
use Kunstmaan\kServer\Provider\ProcessProvider;
use Kunstmaan\kServer\Provider\FileSystemProvider;

/**
 * ApacheSkeleton
 */
class ApacheSkeleton extends AbstractSkeleton
{

    /**
     * @return string
     */
    public function getName()
    {
        return "apache";
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
        $process->executeCommand("rsync -avh " . $this->getVhostTemplateDir() . " " . $filesystem->getProjectConfigDirectory($project->getName()), $output);
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
        /** @var $process ProcessProvider */
        $process = $app["process"];
        $process->executeCommand("rm -Rf " . $this->getCompiledVhostConfigDir($app, $project, $output), $output);
        /** @var $filesystem FileSystemProvider */
        $filesystem = $app["filesystem"];
        $filesystem->createCompiledVhostConfigDirectory($project, $output);

        $finder = new Finder();
        $finder->files()->in($this->getVhostSharedConfigDir($app, $project))->name("*.conf.twig");
        $sharedConfigs = array();
        foreach ($finder as $sharedConfig) {
            $shared = $app['twig']->render($this->getVhostSharedConfigDir($app, $project) . "/" . $sharedConfig->getFilename(), array(
                "project" => $project
            ));
            file_put_contents($this->getCompiledVhostSharedConfigDir($app, $project, $output) . "/" .str_replace(".twig", "", $sharedConfig->getFilename()), $shared);
        }
        $finder = new Finder();
        $finder->files()->in($this->getVhostNoSSLConfigDir($app, $project))->name("*.conf.twig");
        $nosslConfigs = array();
        foreach ($finder as $nosslConfig) {
            $nossl = $app['twig']->render($this->getVhostNoSSLConfigDir($app, $project) . "/" .$nosslConfig->getFilename(), array(
                "project" => $project
            ));
            file_put_contents($this->getCompiledVhostNoSSlConfigDir($app, $project, $output) . "/" .str_replace(".twig", "", $nosslConfig->getFilename()), $nossl);
        }
        $finder = new Finder();
        $finder->files()->in($this->getVhostSSLConfigDir($app, $project))->name("*.conf.twig");
        $sslConfig = array();
        foreach ($finder as $sslConfig) {
            $ssl = $app['twig']->render($this->getVhostSSLConfigDir($app, $project) . "/" .$sslConfig->getFilename(), array(
                "project" => $project
            ));
            file_put_contents($this->getCompiledVhostSSLConfigDir($app, $project, $output) . "/" .str_replace(".twig", "", $sslConfig->getFilename()), $ssl);
        }
        $vhost = $app['twig']->render($this->getVhostConfigDir($app, $project) . '/vhost.conf.twig', array(
            "project" => $project
        ));
        file_put_contents($this->getCompiledVhostConfigDir($app, $project, $output) . "/vhost.conf", $vhost);
        $process->executeCommand("ln -sf " . $this->getCompiledVhostConfigDir($app, $project, $output) . "/vhost.conf /etc/apache2/sites-available/" . $project->getName(), $output);
        $process->executeCommand("a2ensite " . $project->getName(), $output);
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
     * @param Project $project The project
     * @param array   &$config The configuration array
     */
    public function writeConfig(Project $project, &$config)
    {
        // TODO: Implement writeConfig() method.
    }

    /**
     * @param Project $project The project
     * @param array   &$config The configuration array
     */
    public function loadConfig(Project $project, &$config)
    {
        // TODO: Implement loadConfig() method.
    }

    /**
     * @return string
     */
    private function getVhostTemplateDir()
    {
        return __DIR__ . "/../../../../templates/apache";
    }

    /**
     * @param Application $app     The application
     * @param Project     $project The project
     *
     * @return string
     */
    private function getVhostConfigDir(Application $app, Project $project)
    {
        /** @var $filesystem FileSystemProvider */
        $filesystem = $app["filesystem"];

        return $filesystem->getProjectConfigDirectory($project->getName()). "/apache";
    }

    /**
     * @param Application $app     The application
     * @param Project     $project The project
     * @param string      $type    The type
     *
     * @return string
     */
    private function getVhostConfigDirForType(Application $app, Project $project, $type)
    {
        return $this->getVhostConfigDir($app, $project) . "/$type";
    }

    /**
     * @param Application $app     The application
     * @param Project     $project The project
     *
     * @return string
     */
    private function getVhostSharedConfigDir(Application $app, Project $project)
    {
        return $this->getVhostConfigDirForType($app, $project, "shared");
    }

    /**
     * @param Application $app     The application
     * @param Project     $project The project
     *
     * @return string
     */
    private function getVhostNoSSLConfigDir(Application $app, Project $project)
    {
        return $this->getVhostConfigDirForType($app, $project, "nossl");
    }

    /**
     * @param Application $app     The application
     * @param Project     $project The project
     *
     * @return string
     */
    private function getVhostSSLConfigDir(Application $app, Project $project)
    {
        return $this->getVhostConfigDirForType($app, $project, "ssl");
    }

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     *
     * @return string
     */
    private function getCompiledVhostConfigDir(Application $app, Project $project, OutputInterface $output)
    {
        /** @var $filesystem FileSystemProvider */
        $filesystem = $app["filesystem"];

        return $filesystem->getCompiledVhostConfigDirectory($project, $output);
    }

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param string          $type    The type
     * @param OutputInterface $output  The command output stream
     *
     * @return string
     */
    private function getCompiledVhostConfigDirForType(Application $app, Project $project, $type, OutputInterface $output)
    {
        return $this->getCompiledVhostConfigDir($app, $project, $output) . "/$type";
    }

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     *
     * @return string
     */
    private function getCompiledVhostSharedConfigDir(Application $app, Project $project, OutputInterface $output)
    {
        return $this->getCompiledVhostConfigDirForType($app, $project, "shared", $output);
    }

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     *
     * @return string
     */
    private function getCompiledVhostNoSSLConfigDir(Application $app, Project $project, OutputInterface $output)
    {
        return $this->getCompiledVhostConfigDirForType($app, $project, "nossl", $output);
    }

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     *
     * @return string
     */
    private function getCompiledVhostSSLConfigDir(Application $app, Project $project, OutputInterface $output)
    {
        return $this->getCompiledVhostConfigDirForType($app, $project, "ssl", $output);
    }
}
