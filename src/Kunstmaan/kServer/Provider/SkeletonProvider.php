<?php

namespace Kunstmaan\kServer\Provider;

use Kunstmaan\kServer\Helper\OutputUtil;

use Cilex\ServiceProviderInterface;
use Kunstmaan\kServer\Skeleton\AbstractSkeleton;
use Kunstmaan\kServer\Skeleton\SkeletonInterface;
use Kunstmaan\kServer\Entity\Project;
use Symfony\Component\Console\Output\OutputInterface;
use RuntimeException;
use Cilex\Application;

/**
 * SkeletonProvider
 */
class SkeletonProvider implements ServiceProviderInterface
{

    /**
     * @var Application
     */
    private $app;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Registers services on the given app.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
        $app['skeleton'] = $this;
        $this->app = $app;
    }

    /**
     * @param Project          $project  The project
     * @param AbstractSkeleton $skeleton The skeleton
     * @param OutputInterface  $output   The command output stream
     */
    public function applySkeleton(Project $project, AbstractSkeleton $skeleton, OutputInterface $output)
    {
        OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, "Applying " . get_class($skeleton) . " to " . $project->getName());
        $project->addDependency($skeleton);
        $skeleton->create($this->app, $project, $output);
    }

    /**
     * @param string $skeletonname
     *
     * @return SkeletonInterface
     *
     * @throws \RuntimeException
     */
    public function findSkeleton($skeletonname)
    {
        if (isset($this->app["config"]["skeletons"][$skeletonname])) {
            $skeleton = $this->app["config"]["skeletons"][$skeletonname];

            return new $skeleton($this->app, $this->output);
        }
        throw new RuntimeException("Skeleton not found!");
    }
}