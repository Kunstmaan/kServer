<?php
namespace Kunstmaan\kServer\Skeleton;

use Cilex\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Kunstmaan\kServer\Entity\Project;
use Kunstmaan\kServer\Provider\FileSystemProvider;

class MySQLSkeleton implements SkeletonInterface
{
    /**
     * @return string
     */
    public function getName()
    {
        return "mysql";
    }

    /**
     * @param \Cilex\Application $app
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return mixed
     */
    public function create(Application $app, Project $project, OutputInterface $output)
    {
        /** @var $filesystem FileSystemProvider */
        $filesystem = $app["filesystem"];
        $filesystem->createMySQLBackupDirectory($project, $output);
    }

    /**
     * @param \Cilex\Application $app
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return mixed
     */
    public function maintenance(Application $app, Project $project, OutputInterface $output)
    {
        // TODO: Implement maintenance() method.
    }

    /**
     * @param \Cilex\Application $app
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return mixed
     */
    public function preBackup(Application $app, Project $project, OutputInterface $output)
    {
        // TODO: Implement preBackup() method.
    }

    /**
     * @param \Cilex\Application $app
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return mixed
     */
    public function postBackup(Application $app, Project $project, OutputInterface $output)
    {
        // TODO: Implement postBackup() method.
    }

    /**
     * @param \Cilex\Application $app
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return mixed
     */
    public function preRemove(Application $app, Project $project, OutputInterface $output)
    {
        // TODO: Implement preRemove() method.
    }

    /**
     * @param \Cilex\Application $app
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return mixed
     */
    public function postRemove(Application $app, Project $project, OutputInterface $output)
    {
        // TODO: Implement postRemove() method.
    }


}
