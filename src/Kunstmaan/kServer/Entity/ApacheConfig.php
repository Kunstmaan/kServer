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
 * ApacheConfig
 */
class ApacheConfig
{

    /**
     * @var string
     */
    private $url;

    /**
     * @var string[]
     */
    private $aliases;

    /**
     * @var string
     */
    private $webDir;

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string[] $aliases
     */
    public function setAliases(array $aliases)
    {
        $this->aliases = $aliases;
    }

    /**
     * @return string[]
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * @param string $webDir
     */
    public function setWebDir($webDir)
    {
        $this->webDir = $webDir;
    }

    /**
     * @return string
     */
    public function getWebDir()
    {
        return $this->webDir;
    }

}
