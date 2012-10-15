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
 * PostgreSQLConfig
 */
class PostgreSQLConfig
{

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @param string $mysqlHost
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $mysqlPassword
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param int $mysqlPort
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

}
