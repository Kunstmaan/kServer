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

    const NAME = "base";

    /**
     * @return string
     */
    public function getName()
    {
        return BaseSkeleton::NAME;
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
        $filesystem->createDirectory($project, $output, 'current/web');
        {
            $permissionDefinition = new PermissionDefinition();
            $permissionDefinition->setName("root");
            $permissionDefinition->setPath("/");
            $permissionDefinition->setOwnership("-R " . $project->getName() . "." . $project->getName());
            $permissionDefinition->addAcl("-R -m user::rwx");
            $permissionDefinition->addAcl("-R -m group::---");
            $permissionDefinition->addAcl("-R -m other::---");
            $project->addPermissionDefinition($permissionDefinition);
        }
        $filesystem->createDirectory($project, $output, '.ssh');
        {
            $permissionDefinition = new PermissionDefinition();
            $permissionDefinition->setName("ssh");
            $permissionDefinition->setPath("/.ssh");
            $permissionDefinition->setOwnership("-R " . $project->getName() . "." . $project->getName());
            $permissionDefinition->addAcl("-R -m user::rwX");
            $permissionDefinition->addAcl("-R -m group::---");
            $permissionDefinition->addAcl("-R -m other::---");
            $permissionDefinition->addAcl("-R -m m::---");
            $project->addPermissionDefinition($permissionDefinition);
        }
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
     * @param Project      $project The project
     * @param \ArrayObject $config  The configuration array
     */
    public function writeConfig(Project $project, \ArrayObject $config)
    {
        $config["name"] = $project->getName();
        foreach ($project->getPermissionDefinitions() as $pd) {
            $config["permissions"][$pd->getName()]["path"] = $pd->getPath();
            $config["permissions"][$pd->getName()]["ownership"] = $pd->getOwnership();
            $config["permissions"][$pd->getName()]["acl"] = $pd->getAcl();
        }
        $config["backup"]["excluded"] = $project->getExcludedFromBackup();
    }

    /**
     * @param Project      $project The project
     * @param \ArrayObject $config  The configuration array
     */
    public function loadConfig(Project $project, \ArrayObject $config)
    {
        if (isset($config["backup"]["excluded"])) {
            foreach ($config["backup"]["excluded"] as $excluded) {
                $project->addExcludedFromBackup($excluded);
            }
        }

        foreach ($config["permissions"] as $name => $pdarr) {
            $pd = new PermissionDefinition();
            $pd->setName($name);
            $pd->setPath($pdarr['path']);
            $pd->setOwnership($pdarr['ownership']);
            foreach ($pdarr["acl"] as $acl) {
                $pd->addAcl($acl);
            }
            $project->addPermissionDefinition($pd);
        }
    }


    /**
     * @param \Cilex\Application $app
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return string[]
     */
    public function dependsOn(Application $app, Project $project, OutputInterface $output)
    {
        return array();
    }
}
