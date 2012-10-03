<?php
namespace Kunstmaan\kServer\Skeleton;

use Symfony\Component\Console\Output\OutputInterface;
use Kunstmaan\kServer\Entity\PermissionDefinition;
use Cilex\Application;
use Kunstmaan\kServer\Entity\Project;
use Kunstmaan\kServer\Provider\FileSystemProvider;
use Kunstmaan\kServer\Provider\PermissionsProvider;

/**
 * BaseSkeleton
 */
class BaseSkeleton extends AbstractSkeleton
{

    /**
     * @return string
     */
    public function getName()
    {
        return "base";
    }

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed|void
     */
    public function create(Application $app, Project $project, OutputInterface $output)
    {
        /** @var $filesystem FileSystemProvider */
        $filesystem = $app["filesystem"];
        $filesystem->createProjectConfigDirectory($project, $output);
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
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     */
    public function permissions(Application $app, Project $project, OutputInterface $output)
    {
        /** @var $permission PermissionsProvider */
        $permission = $app["permission"];
        $permission->createGroupIfNeeded($project->getName(), $output);
        $permission->createUserIfNeeded($project->getName(), $project->getName(), $output);
        $permission->applyOwnership($project, $output);
        $permission->applyPermissions($project, $output);
    }

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed|void
     */
    public function maintenance(Application $app, Project $project, OutputInterface $output)
    {
        $this->permissions($app, $project, $output);
    }

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed|void
     */
    public function preBackup(Application $app, Project $project, OutputInterface $output)
    {
    }

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed|void
     */
    public function postBackup(Application $app, Project $project, OutputInterface $output)
    {
    }

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed|void
     */
    public function preRemove(Application $app, Project $project, OutputInterface $output)
    {
    }

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed|void
     */
    public function postRemove(Application $app, Project $project, OutputInterface $output)
    {
        /** @var $permission PermissionsProvider */
        $permission = $app["permission"];
        $permission->removeUser($project->getName(), $project->getName(), $output);
    }

    /**
     * @param Project $project The project
     * @param array   &$config The configuration array
     */
    public function writeConfig(Project $project, &$config)
    {
        // TODO: Implement writeConfig() method.
    }

    /**
     * @param Project $project The project
     * @param array   &$config The configuration array
     */
    public function loadConfig(Project $project, &$config)
    {
        // TODO: Implement readConfig() method.
    }


}