<?php
namespace Kunstmaan\kServer\Command;

use Cilex\Command\Command;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Cilex\Application;
use Kunstmaan\kServer\Provider\FileSystemProvider;
use Kunstmaan\kServer\Provider\ProjectConfigProvider;
use Kunstmaan\kServer\Provider\SkeletonProvider;
use Kunstmaan\kServer\Provider\ProcessProvider;
use Kunstmaan\kServer\Provider\PermissionsProvider;
use Kunstmaan\kServer\Provider\DialogProvider;

/**
 * AbstractCommand
 */
abstract class AbstractCommand extends Command
{

    /**
     * @var FileSystemProvider
     */
    protected $filesystem;

    /**
     * @var ProjectConfigProvider
     */
    protected $projectConfig;

    /**
     * @var SkeletonProvider
     */
    protected $skeleton;

    /**
     * @var ProcessProvider
     */
    protected $process;

    /**
     * @var PermissionsProvider
     */
    protected $permission;

    /**
     * @var DialogProvider
     */
    protected $dialog;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->filesystem = $this->getService('filesystem');
        $this->projectConfig = $this->getService('projectconfig');
        $this->skeleton = $this->getService('skeleton');
        $this->process = $this->getService('process');
        $this->permission = $this->getService('permission');
        $this->dialog = $this->getService('dialog');
        $this->output = $output;
    }

}
