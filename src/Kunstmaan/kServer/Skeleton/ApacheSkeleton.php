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
class ApacheSkeleton extends AbstractSkeleton
{

    const NAME = "apache";

    /**
     * @return string
     */
    public function getName()
    {
        return ApacheSkeleton::NAME;
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
        /** @var $console \Symfony\Component\Console\Application */
        $console = $app['console'];
        /** @var $dialog DialogHelper */
        $dialog = $console->getHelperSet()->get('dialog');

        $apacheConfig = new ApacheConfig();

        { // url
            $defaultUrl = "www.".$project->getName().".com";
            $apacheConfig->setUrl($dialog->ask($output, '      <question>Enter the base url: ['.$defaultUrl.'] </question>', $defaultUrl));
        }

        { // url aliases
            $aliases = array();
            $alias = null;
            while (1==1) {
                $alias = $dialog->ask($output, '      <question>Add an url alias (leave empty to stop adding): </question>');
                if (empty($alias)) {
                    break;
                } else {
                    $aliases[] = $alias;
                }
            }
            $apacheConfig->setAliases($aliases);
        }

        $apacheConfig->setWebDir("web");

        $project->setConfiguration(ApacheSkeleton::NAME, $apacheConfig);
        $process->executeCommand("rsync -avh " . $this->getVhostTemplateDir() . " " . $filesystem->getProjectConfigDirectory($project->getName()), $output);

        $permissionDefinition = new PermissionDefinition();
        $permissionDefinition->setName("apache");
        $permissionDefinition->setPath("/");
        $permissionDefinition->setOwnership("-R " . $project->getName() . "." . $project->getName());
        $permissionDefinition->addAcl("-R -m u:" . $app["config"]["apache"]["user"] . ":r-X");
        $project->addPermissionDefinition($permissionDefinition);

        $filesystem->createDirectory($project, $output, "apachelogs");
        $project->setLogPath("apachelogs");
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

        $localAliases = array();
        $localAliases[] = $project->getName() . "." . $app["config"]["hostname"];
        $localAliases[] = "www." . $project->getName() . "." . $app["config"]["hostname"];

        $apacheConf = $project->getConfiguration(ApacheSkeleton::NAME);

        $configRenderParams = array(
                "project" => $project,
                "configLocation" => "vhost.d/".$project->getName(),
                "serverAdmin" => "support@kunstmaan.be",
                "apacheConfig" => $apacheConf,
                "localAliases" => $localAliases,
                "documentRoot" => $app["config"]["projects"]["path"] . "/" . $project->getName() . "/current/" . $apacheConf->getWebDir());

        $finder = new Finder();
        $finder->files()->in($this->getVhostSharedConfigDir($app, $project))->name("*.conf.twig");
        $sharedConfigs = array();
        foreach ($finder as $sharedConfig) {
            $shared = $app['twig']->render($this->getVhostSharedConfigDir($app, $project) . "/" . $sharedConfig->getFilename(), $configRenderParams);
            file_put_contents($this->getCompiledVhostSharedConfigDir($app, $project, $output) . "/" .str_replace(".twig", "", $sharedConfig->getFilename()), $shared);
        }
        $finder = new Finder();
        $finder->files()->in($this->getVhostNoSSLConfigDir($app, $project))->name("*.conf.twig");
        $nosslConfigs = array();
        foreach ($finder as $nosslConfig) {
            $nossl = $app['twig']->render($this->getVhostNoSSLConfigDir($app, $project) . "/" .$nosslConfig->getFilename(), $configRenderParams);
            file_put_contents($this->getCompiledVhostNoSSlConfigDir($app, $project, $output) . "/" .str_replace(".twig", "", $nosslConfig->getFilename()), $nossl);
        }
        $finder = new Finder();
        $finder->files()->in($this->getVhostSSLConfigDir($app, $project))->name("*.conf.twig");
        $sslConfig = array();
        foreach ($finder as $sslConfig) {
            $ssl = $app['twig']->render($this->getVhostSSLConfigDir($app, $project) . "/" .$sslConfig->getFilename(), $configRenderParams);
            file_put_contents($this->getCompiledVhostSSLConfigDir($app, $project, $output) . "/" .str_replace(".twig", "", $sslConfig->getFilename()), $ssl);
        }
        $vhost = $app['twig']->render($this->getVhostConfigDir($app, $project) . '/vhost.conf.twig', $configRenderParams);
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
     * @param Project      $project The project
     * @param \ArrayObject $config  The configuration array
     */
    public function writeConfig(Project $project, \ArrayObject $config)
    {
        /* @var $apacheConfig ApacheConfig */
        $apacheConfig = $project->getConfiguration(ApacheSkeleton::NAME);
        if (!is_null($apacheConfig->getUrl())) {
            $config["url"] = $apacheConfig->getUrl();
        }
        if (!is_null($apacheConfig->getAliases())) {
            $config["aliases"] = $apacheConfig->getAliases();
        }
        if (!is_null($apacheConfig->getWebDir())) {
            $config["webdir"] = $apacheConfig->getWebDir();
        }
    }

    /**
     * @param Project      $project The project
     * @param \ArrayObject $config  The configuration array
     */
    public function loadConfig(Project $project, \ArrayObject $config)
    {
        $apacheConfig = new ApacheConfig();
        if (isset($config["url"])) {
            $apacheConfig->setUrl($config["url"]);
        }
        if (isset($config["aliases"])) {
            $apacheConfig->setAliases($config["aliases"]);
        }
        if (isset($config["webdir"])) {
            $apacheConfig->setWebDir($config["webdir"]);
        }
        $project->setConfiguration(ApacheSkeleton::NAME, $apacheConfig);
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

    /**
     * @param \Cilex\Application $app
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return string[]
     */
    public function dependsOn(Application $app, Project $project, OutputInterface $output)
    {
        return array(
            "base"
        );
    }
}
