<?php
namespace Kunstmaan\kServer\Entity;

use Kunstmaan\kServer\Helper\OutputUtil;

use Kunstmaan\kServer\Provider\SkeletonProvider;

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
     * @var \ArrayObject
     */
    private $configurations;

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
     * @param string $name
     *
     * @return mixed
     */
    public function getConfiguration($name)
    {
        return $this->configurations[$name];
    }

    /**
     * @param string $name          The configuration internal name
     * @param mixed  $configuration The configuration object
     */
    public function setConfiguration($name, $configuration)
    {
        $this->configurations[$name] = $configuration;
    }

}
