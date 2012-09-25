<?php
namespace Kunstmaan\kServer\Entity;


use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Yaml;
use Kunstmaan\kServer\Skeleton\SkeletonInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @param string $name
     * @param string $configPath
     */
    function __construct($name, $configPath)
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
     * @param \Kunstmaan\kServer\Skeleton\SkeletonInterface $skeleton
     */
    public function addDependency(SkeletonInterface $skeleton)
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
     * @param \Symfony\Component\Console\Output\OutputInterface $output
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
        $dumper = new Dumper();
        $yaml = $dumper->dump($config, 5);
        file_put_contents($this->getConfigPath(), $yaml);
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function loadConfig(OutputInterface $output)
    {
        $output->writeln("<comment>      > Loading the project config from " . $this->getConfigPath() . "</comment>");
        $config = Yaml::parse($this->getConfigPath());
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
