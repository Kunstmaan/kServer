<?php
namespace Kunstmaan\kServer\Entity;


use Symfony\Component\Yaml\Dumper;
use Kunstmaan\kServer\Skeleton\SkeletonInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Project
{

    private $configPath;
    private $name;

    /**
     * @var string[]
     */
    private $dependencies;


    function __construct($name, $configPath)
    {
        $this->name = $name;
        $this->configPath = $configPath;
    }

    public function getName(){
        return $this->name;
    }

    public function addDependency(SkeletonInterface $skeleton)
    {
        $this->dependencies[$skeleton->getName()] = get_class($skeleton);
    }

    public function getDependencies()
    {
        return $this->dependencies;
    }

    public function setConfigPath($configPath)
    {
        $this->configPath = $configPath;
    }

    public function getConfigPath()
    {
        return $this->configPath;
    }



    public function writeConfig(OutputInterface $output){
        $output->writeln("<comment>      > Writing the project config to ".$this->getConfigPath()."</comment>");
        $config = array();
        $config["kserver"]["name"] = $this->getName();
        $config["kserver"]["dependencies"] = $this->getDependencies();
        $dumper = new Dumper();
        $yaml = $dumper->dump($config, 3);
        file_put_contents($this->getConfigPath(), $yaml);
    }

}
