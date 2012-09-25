<?php

namespace Kunstmaan\kServer\Provider;

use Cilex\ServiceProviderInterface;
use Kunstmaan\kServer\Skeleton\SkeletonInterface;
use Kunstmaan\kServer\Entity\Project;
use Symfony\Component\Console\Output\OutputInterface;
use RuntimeException;
use Cilex\Application;

class SkeletonProvider implements ServiceProviderInterface
{

    /**
     * @var Application
     */
    private $app;

    /**
     * Registers services on the given app.
     *
     * @param Application $app An Application instance
     */
    function register(Application $app)
    {
        $app['skeleton'] = $this;
        $this->app = $app;
    }

    /**
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Kunstmaan\kServer\Skeleton\SkeletonInterface $skeleton
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    function applySkeleton(Project $project, SkeletonInterface $skeleton, OutputInterface $output)
    {
        $output->writeln("<comment>      > Applying " . get_class($skeleton) . " to " . $project->getName() . " </comment>");
        $skeleton->create($this->app, $project, $output);
    }
}