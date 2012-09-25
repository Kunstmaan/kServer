<?php
namespace Kunstmaan\kServer\Command;


use Cilex\Command\Command;
use Cilex\Application;

abstract class kServerCommand extends Command
{

    /** @var $filesystem FileSystemProvider */
    protected $filesystem;
    /** @var $projectConfig ProjectConfigProvider */
    protected $projectConfig;
    /** @var $skeleton SkeletonProvider */
    protected $skeleton;

    protected function prepareProviders()
    {
        $this->filesystem = $this->getService('filesystem');
        $this->projectConfig = $this->getService('projectconfig');
        $this->skeleton = $this->getService('skeleton');
    }

}
