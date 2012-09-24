<?php
namespace Kunstmaan\kServer\Skeleton;


use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Application;
use Kunstmaan\kServer\Entity\Project;
use Kunstmaan\kServer\Provider\FileSystemProvider;


class BaseSkeleton implements SkeletonInterface
{

    public function getName(){
        return "base";
    }

    public function create(Application $app, Project $project, OutputInterface $output)
    {
        /** @var $filesystem FileSystemProvider */
        $filesystem = $app["filesystem"];
        $filesystem->createProjectConfigDirectory($project, $output);
        $project->addDependency($this);
    }


}
