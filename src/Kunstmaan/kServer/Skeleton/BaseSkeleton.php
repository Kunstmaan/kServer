<?php
namespace Kunstmaan\kServer\Skeleton;


use Symfony\Component\Console\Output\OutputInterface;
use Kunstmaan\kServer\Entity\PermissionDefinition;
use Cilex\Application;
use Kunstmaan\kServer\Entity\Project;
use Kunstmaan\kServer\Provider\FileSystemProvider;


class BaseSkeleton implements SkeletonInterface
{

    public function getName(){
        return "base";
    }

    /**
     *     permissions:
    - root:
    path:       "/"
    ownership:  "-R $superuser.$group"
    acl:
    - "-R -m user::rw-"
    - "-R -m group::---"
    - "-R -m other::---"
     */

    public function create(Application $app, Project $project, OutputInterface $output)
    {
        /** @var $filesystem FileSystemProvider */
        $filesystem = $app["filesystem"];
        $filesystem->createProjectConfigDirectory($project, $output);
        $project->addDependency($this);
        $permissionDefinition = new PermissionDefinition();
        $permissionDefinition->setName("root");
        $permissionDefinition->setPath("/");
        $permissionDefinition->setOwnership('-R $superuser.$group');
        $permissionDefinition->addAcl("-R -m user::rw-");
        $permissionDefinition->addAcl("-R -m group::---");
        $permissionDefinition->addAcl("-R -m other::---");
        $project->addPermissionDefinition($permissionDefinition);
    }


}
