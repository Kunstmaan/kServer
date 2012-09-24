<?php
namespace Kunstmaan\kServer\Skeleton;


use Symfony\Component\Console\Output\OutputInterface;
use Kunstmaan\kServer\Entity\PermissionDefinition;
use Cilex\Application;
use Kunstmaan\kServer\Entity\Project;
use Kunstmaan\kServer\Provider\FileSystemProvider;
use Kunstmaan\kServer\Provider\PermissionsProvider;



class BaseSkeleton implements SkeletonInterface
{

    public function getName(){
        return "base";
    }

    /**
     * @param \Cilex\Application $app
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
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
        $permissionDefinition->setOwnership("-R root." . $project->getName());
        $permissionDefinition->addAcl("-R -m user::rw-");
        $permissionDefinition->addAcl("-R -m group::---");
        $permissionDefinition->addAcl("-R -m other::---");
        $project->addPermissionDefinition($permissionDefinition);
    }

    /**
     * @param \Cilex\Application $app
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function permissions(Application $app, Project $project, OutputInterface $output){
        /** @var $permission PermissionsProvider */
        $permission = $app["permission"];
        $permission->createGroupIfNeeded($project->getName(), $output);
        $permission->createUserIfNeeded($project->getName(), $project->getName() , $output);
        $permission->applyOwnership($project, $output);
        $permission->applyPermissions($project, $output);
    }

    /**
     * @param \Cilex\Application $app
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return mixed|void
     */
    public function maintenance(Application $app, Project $project, OutputInterface $output){
        $this->permissions($app, $project, $output);
    }


}