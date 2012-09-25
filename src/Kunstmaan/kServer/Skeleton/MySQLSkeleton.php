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
        $project->setMysqlUser("x");
        $project->setMysqlPassword("y");
        $project->setMysqlHost("z");
        $project->setMysqlPort(1);
    }

    /**
     * @param \Cilex\Application $app
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return mixed
     */
    public function maintenance(Application $app, Project $project, OutputInterface $output)
    {
        //$pdo = new PDO('mysql:host=example.com;dbname=database', 'user', 'password');



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

    /**
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param $config
     */
    public function writeConfig(Project $project, &$config){
        if (!is_null($project->getMysqlUser())) { $config["kserver"]["mysql"]["user"] = $project->getMysqlUser(); }
        if (!is_null($project->getMysqlPassword())) { $config["kserver"]["mysql"]["password"] = $project->getMysqlPassword(); }
        if (!is_null($project->getMysqlHost())) { $config["kserver"]["mysql"]["host"] = $project->getMysqlHost(); }
        if (!is_null($project->getMysqlPort())) { $config["kserver"]["mysql"]["port"] = $project->getMysqlPort(); }
    }

    /**
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param $config
     */
    public function loadConfig(Project $project, &$config){
        if (isset($config["kserver"]["mysql"]["user"])) { $project->setMysqlUser($config["kserver"]["mysql"]["user"]); }
        if (isset($config["kserver"]["mysql"]["password"])) { $project->setMysqlPassword($config["kserver"]["mysql"]["password"]); }
        if (isset($config["kserver"]["mysql"]["host"])) { $project->setMysqlHost($config["kserver"]["mysql"]["host"]); }
        if (isset($config["kserver"]["mysql"]["port"])) { $project->setMysqlPort($config["kserver"]["mysql"]["port"]); }
    }

}
