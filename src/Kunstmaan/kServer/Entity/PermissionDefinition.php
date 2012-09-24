<?php
namespace Kunstmaan\kServer\Entity;


class PermissionDefinition
{

    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $path;
    /**
     * @var string
     */
    private $ownership;
    /**
     * @var string[]
     */
    private $acl;

    /**
     * @param string $acl
     */
    public function addAcl($acl)
    {
        $this->acl[] = $acl;
    }

    public function getAcl()
    {
        return $this->acl;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $ownership
     */
    public function setOwnership($ownership)
    {
        $this->ownership = $ownership;
    }

    /**
     * @return string
     */
    public function getOwnership()
    {
        return $this->ownership;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }


}
