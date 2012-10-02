<?php
namespace Kunstmaan\kServer\Skeleton;

use Cilex\Application;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Output\OutputInterface;
use Kunstmaan\kServer\Entity\Project;
use Kunstmaan\kServer\Provider\ProcessProvider;
use Kunstmaan\kServer\Provider\FileSystemProvider;

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
     * @param \Cilex\Application $app
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
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
     * @param \Cilex\Application $app
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
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
        foreach ($finder as $sharedConfig){
            $shared = $app['twig']->render($this->getVhostSharedConfigDir($app,$project) . "/" .$sharedConfig->getFilename(), array(
                "project" => $project
            ));
            file_put_contents($this->getCompiledVhostSharedConfigDir($app,$project, $output) . "/" .str_replace(".twig", "", $sharedConfig->getFilename()), $shared);
        }
        $finder = new Finder();
        $finder->files()->in($this->getVhostNoSSLConfigDir($app, $project))->name("*.conf.twig");
        $nosslConfigs = array();
        foreach ($finder as $nosslConfig){
            $nossl = $app['twig']->render($this->getVhostNoSSLConfigDir($app,$project) . "/" .$nosslConfig->getFilename(), array(
                "project" => $project
            ));
            file_put_contents($this->getCompiledVhostNoSSlConfigDir($app,$project, $output) . "/" .str_replace(".twig", "", $nosslConfig->getFilename()), $nossl);
        }
        $finder = new Finder();
        $finder->files()->in($this->getVhostSSLConfigDir($app, $project))->name("*.conf.twig");
        $sslConfig = array();
        foreach ($finder as $sslConfig){
            $ssl = $app['twig']->render($this->getVhostSSLConfigDir($app,$project) . "/" .$sslConfig->getFilename(), array(
                "project" => $project
            ));
            file_put_contents($this->getCompiledVhostSSLConfigDir($app,$project, $output) . "/" .str_replace(".twig", "", $sslConfig->getFilename()), $ssl);
        }
        $vhost = $app['twig']->render($this->getVhostConfigDir($app,$project) . '/vhost.conf.twig', array(
            "project" => $project
        ));
        file_put_contents($this->getCompiledVhostConfigDir($app,$project, $output) . "/vhost.conf", $vhost);
        $process->executeCommand("ln -sf " . $this->getCompiledVhostConfigDir($app,$project, $output) . "/vhost.conf /etc/apache2/sites-available/" . $project->getName(), $output);
        $process->executeCommand("a2ensite " . $project->getName(), $output);
    }

    /**
     * @param \Cilex\Application $app
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return mixed
     */
    public function preBackup(Application $app, Project $project, OutputInterface $output)
    {
        // TODO: Implement preBackup() method.
    }

    /**
     * @param \Cilex\Application $app
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return mixed
     */
    public function postBackup(Application $app, Project $project, OutputInterface $output)
    {
        // TODO: Implement postBackup() method.
    }

    /**
     * @param \Cilex\Application $app
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return mixed
     */
    public function preRemove(Application $app, Project $project, OutputInterface $output)
    {
        // TODO: Implement preRemove() method.
    }

    /**
     * @param \Cilex\Application $app
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return mixed
     */
    public function postRemove(Application $app, Project $project, OutputInterface $output)
    {
        // TODO: Implement postRemove() method.
    }

    /**
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param $config
     */
    public function writeConfig(Project $project, &$config)
    {
        // TODO: Implement writeConfig() method.
    }

    /**
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param $config
     */
    public function loadConfig(Project $project, &$config)
    {
        // TODO: Implement loadConfig() method.
    }


    private function getVhostTemplateDir()
    {
        return __DIR__ . "/../../../../templates/apache";
    }

    private function getVhostConfigDir(Application $app, Project $project)
    {
        /** @var $filesystem FileSystemProvider */
        $filesystem = $app["filesystem"];
        return $filesystem->getProjectConfigDirectory($project->getName()). "/apache";
    }

    private function getVhostConfigDirForType(Application $app, Project $project, $type)
    {
        return $this->getVhostConfigDir($app, $project) . "/$type";
    }

    private function getVhostSharedConfigDir(Application $app, Project $project)
    {
        return $this->getVhostConfigDirForType($app, $project,"shared");
    }

    private function getVhostNoSSLConfigDir(Application $app, Project $project)
    {
        return $this->getVhostConfigDirForType($app, $project,"nossl");
    }

    private function getVhostSSLConfigDir(Application $app, Project $project)
    {
        return $this->getVhostConfigDirForType($app, $project,"ssl");
    }

    private function getCompiledVhostConfigDir(Application $app, Project $project, OutputInterface $output)
    {
        /** @var $filesystem FileSystemProvider */
        $filesystem = $app["filesystem"];
        return $filesystem->getCompiledVhostConfigDirectory($project, $output);
    }

    private function getCompiledVhostConfigDirForType(Application $app, Project $project, $type, OutputInterface $output)
    {
        return $this->getCompiledVhostConfigDir($app, $project, $output) . "/$type";
    }

    private function getCompiledVhostSharedConfigDir(Application $app, Project $project, OutputInterface $output)
    {
        return $this->getCompiledVhostConfigDirForType($app, $project,"shared", $output);
    }

    private function getCompiledVhostNoSSLConfigDir(Application $app, Project $project, OutputInterface $output)
    {
        return $this->getCompiledVhostConfigDirForType($app, $project,"nossl", $output);
    }

    private function getCompiledVhostSSLConfigDir(Application $app, Project $project, OutputInterface $output)
    {
        return $this->getCompiledVhostConfigDirForType($app, $project,"ssl", $output);
    }
}
