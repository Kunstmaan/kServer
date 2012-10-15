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
        $this->resolveDependencies($project,$output);
        $skeleton->create($this->app, $project, $output);
    }

    /**
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    private function resolveDependencies(Project $project, OutputInterface $output){
        $deps = $project->getDependencies();
        foreach ($deps as $skeletonName => $skeletonClass){
            $theSkeleton = $this->findSkeleton($skeletonName);
            $skeletonDeps = $theSkeleton->dependsOn($this->app, $project, $output);
            foreach($skeletonDeps as $skeletonDependencyName){
                if(!isset($deps[$skeletonDependencyName])){
                    $aSkeleton = $this->findSkeleton($skeletonDependencyName);
                    $this->applySkeleton($project, $aSkeleton, $output);
                }
            }
        }
    }

    /**
     * @param string $skeletonname
     *
     * @return AbstractSkeleton
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

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function listSkeletons(OutputInterface $output)
    {
	foreach($this->app["config"]["skeletons"] as $name => $class ){
	    OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, $name);
	}
    }
}
