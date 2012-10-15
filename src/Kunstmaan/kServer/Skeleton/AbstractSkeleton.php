<?php
namespace Kunstmaan\kServer\Skeleton;

use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Application;
use Kunstmaan\kServer\Entity\Project;
use Kunstmaan\kServer\Provider\FileSystemProvider;
use Kunstmaan\kServer\Provider\ProjectConfigProvider;
use Kunstmaan\kServer\Provider\SkeletonProvider;
use Kunstmaan\kServer\Provider\ProcessProvider;
use Kunstmaan\kServer\Provider\PermissionsProvider;

/**
 * AbstractSkeleton
 */
abstract class AbstractSkeleton
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
     * @var OutputInterface
     */
    protected $output;

    /**
     * @param Application     $app    The app
     * @param OutputInterface $output The command output stream
     */
    public function __construct(Application $app, OutputInterface $output)
    {
        $this->filesystem = $app['filesystem'];
        $this->permission = $app['permission'];
        $this->process = $app['process'];
        $this->projectConfig = $app['projectconfig'];
        $this->skeleton = $app['skeleton'];
        $this->output = $output;
    }

    /**
     * @return string
     */
    abstract public function getName();

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed
     */
    abstract public function create(Application $app, Project $project, OutputInterface $output);

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed
     */
    abstract public function maintenance(Application $app, Project $project, OutputInterface $output);

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed
     */
    abstract public function preBackup(Application $app, Project $project, OutputInterface $output);

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed
     */
    abstract public function postBackup(Application $app, Project $project, OutputInterface $output);

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed
     */
    abstract public function preRemove(Application $app, Project $project, OutputInterface $output);

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed
     */
    abstract public function postRemove(Application $app, Project $project, OutputInterface $output);

    /**
     * @param Project      $project The project
     * @param \ArrayObject $config  The configuration array
     */
    abstract public function writeConfig(Project $project, \ArrayObject $config);

    /**
     * @param Project      $project The project
     * @param \ArrayObject $config  The configuration array
     */
    abstract public function loadConfig(Project $project, \ArrayObject $config);

}
