<?php
namespace Kunstmaan\kServer\Entity;

use Symfony\Component\Yaml\Dumper;
use Kunstmaan\kServer\Skeleton\AbstractSkeleton;
use Symfony\Component\Yaml\Yaml;
use Kunstmaan\kServer\Skeleton\SkeletonInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Project
 */
class Project
{

    /**
     * @var string
     */
    private $configPath;

    /**
     * @var string
     */
    private $name;

    /**
     * @var PermissionDefinition[]
     */
    private $permissiondefinitions;

    /**
     * @var string[]
     */
    private $dependencies;

    /**
     * @var string[]
     */
    private $excludedFromBackup;

    /**
     * @var string
     */
    private $mysqlUser;

    /**
     * @var string
     */
    private $mysqlPassword;

    /**
     * @var string
     */
    private $mysqlHost;

    /**
     * @var int
     */
    private $mysqlPort;

    /**
     * @param string $name       The project name
     * @param string $configPath The path of the configuration file
     */
    public function __construct($name, $configPath)
    {
        $this->name = $name;
        $this->configPath = $configPath;
    }

    /**
     * @param PermissionDefinition $pd
     */
    public function addPermissionDefinition(PermissionDefinition $pd)
    {
        $this->permissiondefinitions[] = $pd;
    }

    /**
     * @return PermissionDefinition[]
     */
    public function getPermissionDefinitions()
    {
        return $this->permissiondefinitions;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $filenamePattern
     */
    public function addExcludedFromBackup($filenamePattern)
    {
        $this->excludedFromBackup[] = $filenamePattern;
    }

    /**
     * @return string[]
     */
    public function getExcludedFromBackup()
    {
        return $this->excludedFromBackup;
    }

    /**
     * @param \Kunstmaan\kServer\Skeleton\AbstractSkeleton $skeleton
     */
    public function addDependency(AbstractSkeleton $skeleton)
    {
        $this->dependencies[$skeleton->getName()] = get_class($skeleton);
    }

    /**
     * @return string[]
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * @param string $configPath
     */
    public function setConfigPath($configPath)
    {
        $this->configPath = $configPath;
    }

    /**
     * @return string
     */
    public function getConfigPath()
    {
        return $this->configPath;
    }

    /**
     * @param string $mysqlHost
     */
    public function setMysqlHost($mysqlHost)
    {
        $this->mysqlHost = $mysqlHost;
    }

    /**
     * @return string
     */
    public function getMysqlHost()
    {
        return $this->mysqlHost;
    }

    /**
     * @param string $mysqlPassword
     */
    public function setMysqlPassword($mysqlPassword)
    {
        $this->mysqlPassword = $mysqlPassword;
    }

    /**
     * @return string
     */
    public function getMysqlPassword()
    {
        return $this->mysqlPassword;
    }

    /**
     * @param int $mysqlPort
     */
    public function setMysqlPort($mysqlPort)
    {
        $this->mysqlPort = $mysqlPort;
    }

    /**
     * @return int
     */
    public function getMysqlPort()
    {
        return $this->mysqlPort;
    }

    /**
     * @param string $mysqlUser
     */
    public function setMysqlUser($mysqlUser)
    {
        $this->mysqlUser = $mysqlUser;
    }

    /**
     * @return string
     */
    public function getMysqlUser()
    {
        return $this->mysqlUser;
    }

    /**
     * @param OutputInterface $output
     */
    public function writeConfig(OutputInterface $output)
    {
        $output->writeln("<comment>      > Writing the project config to " . $this->getConfigPath() . "</comment>");
        $config = array();
        $config["kserver"]["name"] = $this->getName();
        $config["kserver"]["dependencies"] = $this->getDependencies();
        foreach ($this->getPermissionDefinitions() as $pd) {
            $config["kserver"]["permissions"][$pd->getName()]["path"] = $pd->getPath();
            $config["kserver"]["permissions"][$pd->getName()]["ownership"] = $pd->getOwnership();
            $config["kserver"]["permissions"][$pd->getName()]["acl"] = $pd->getAcl();
        }
        $config["kserver"]["backup"]["excluded"] = $this->getExcludedFromBackup();

        foreach ($this->getDependencies() as $skeletonclass) {
            /** @var $skeleton SkeletonInterface */
            $skeleton = new $skeletonclass;
            $skeleton->writeConfig($this, $config);
        }

        $dumper = new Dumper();
        $yaml = $dumper->dump($config, 5);
        file_put_contents($this->getConfigPath(), $yaml);
    }


    /**
     * @param string[]        $skeletons Skeletons array
     * @param OutputInterface $output    The command output stream
     */
    public function loadConfig(array $skeletons, OutputInterface $output)
    {
        $output->writeln("<comment>      > Loading the project config from " . $this->getConfigPath() . "</comment>");
        $config = Yaml::parse($this->getConfigPath());

        foreach ($skeletons as $skeletonclass) {
            /** @var $skeleton SkeletonInterface */
            $skeleton = new $skeletonclass;
            $skeleton->loadConfig($this, $config);
        }

        foreach ($config["kserver"]["dependencies"] as $dep) {
            $this->addDependency(new $dep);
        }
        if (isset($config["kserver"]["backup"]["excluded"])) {
            foreach ($config["kserver"]["backup"]["excluded"] as $excluded) {
                $this->addExcludedFromBackup($excluded);
            }
        }

        foreach ($config["kserver"]["permissions"] as $name => $pdarr) {
            $pd = new PermissionDefinition();
            $pd->setName($name);
            $pd->setPath($pdarr['path']);
            $pd->setOwnership($pdarr['ownership']);
            foreach ($pdarr["acl"] as $acl) {
                $pd->addAcl($acl);
            }
            $this->addPermissionDefinition($pd);
        }
    }
}
