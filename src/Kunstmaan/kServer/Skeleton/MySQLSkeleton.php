<?php
namespace Kunstmaan\kServer\Skeleton;

use Cilex\Application;
use Symfony\Component\Finder\Finder;
use PDOException;
use PWGen;
use Symfony\Component\Console\Output\OutputInterface;
use Kunstmaan\kServer\Entity\Project;
use Kunstmaan\kServer\Provider\FileSystemProvider;
use Symfony\Component\Console\Helper\DialogHelper;
use PDO;
use Kunstmaan\kServer\Provider\ProcessProvider;

class MySQLSkeleton extends AbstractSkeleton
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

        /** @var $console \Symfony\Component\Console\Application */
        $console = $app['console'];
        /** @var $dialog DialogHelper */
        $dialog = $console->getHelperSet()->get('dialog');

        $project->setMysqlUser($dialog->ask($output, '      <question>Enter the preferred MySQL username: ['.$project->getName().'] </question>', $project->getName()));

        $pwgen = new PWGen();
        $password = $pwgen->generate();

        $project->setMysqlPassword($dialog->ask($output, '      <question>Enter the preferred MySQL password: ['.$password.'] </question>', $password));
        $project->setMysqlHost($dialog->ask($output, '      <question>Enter the MySQL hostname: [localhost] </question>', 'localhost'));
        $project->setMysqlPort($dialog->ask($output, '      <question>Enter the MySQL port: [3306] </question>', 3306));
    }

    /**
     * @param \Cilex\Application $app
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return mixed
     */
    public function maintenance(Application $app, Project $project, OutputInterface $output)
    {
        try {
            $pdo = new PDO('mysql:host='.$project->getMysqlHost().';port='.$project->getMysqlPort().';dbname='.$project->getMysqlUser(), $project->getMysqlUser(), $project->getMysqlPassword());
        } catch(PDOException $exLoginTest){
            $output->writeln("<comment>      > Cannot connect as " . $project->getMysqlUser() . ", let's test if the database exists (".$exLoginTest->getMessage().")</comment>");
            try {
                $pdo = new PDO('mysql:host='.$project->getMysqlHost().';port='.$project->getMysqlPort().';dbname='.$project->getMysqlUser(), $app["config"]["mysql"]["rootuser"], $app["config"]["mysql"]["rootpassword"]);
            } catch(PDOException $exDBTest){
                $output->writeln("<comment>      > Cannot connect to the " . $project->getMysqlUser() . " database as the root user as well, let's create it. (".$exDBTest->getMessage().")</comment>");
                $pdo = new PDO('mysql:host='.$project->getMysqlHost().';port='.$project->getMysqlPort(), $app["config"]["mysql"]["rootuser"], $app["config"]["mysql"]["rootpassword"]);
                $pdo->exec("create database " . $project->getMysqlUser() . " CHARACTER SET utf8 COLLATE utf8_general_ci;");
                $finder = new Finder();
                /** @var $filesystem FileSystemProvider */
                $filesystem = $app["filesystem"];
                $finder->files()->in($filesystem->getMySQLBackupDirectory($project, $output))->name("mysql.dmp.gz");
                if (sizeof(iterator_to_array($finder)) > 0){
                    /** @var $process ProcessProvider */
                    $process = $app["process"];
                    $process->executeCommand('gzip -dc '.$filesystem->getMySQLBackupDirectory($project, $output).'/mysql.dmp.gz | mysql -h '.$project->getMysqlHost().' -P '.$project->getMysqlPort().' -u '.$app["config"]["mysql"]["rootuser"].' -p'.$app["config"]["mysql"]["rootpassword"].' '.$project->getMysqlUser(), $output);
                }
            }
            $pdo->exec("GRANT ALL PRIVILEGES ON ".$project->getMysqlUser().".* TO ".$project->getMysqlUser()."@localhost IDENTIFIED BY '".$project->getMysqlPassword()."'");
            $pdo->exec("GRANT ALL PRIVILEGES ON ".$project->getMysqlUser().".* TO ".$project->getMysqlUser()."@'%%' IDENTIFIED BY '".$project->getMysqlPassword()."'");
        }
    }

    /**
     * @param \Cilex\Application $app
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return mixed
     */
    public function preBackup(Application $app, Project $project, OutputInterface $output)
    {
        /** @var $process ProcessProvider */
        $process = $app["process"];
        /** @var $filesystem FileSystemProvider */
        $filesystem = $app["filesystem"];
        $process->executeCommand('rm -f '.$filesystem->getMySQLBackupDirectory($project, $output).'/mysql.dmp', $output);
        $process->executeCommand('rm -f '.$filesystem->getMySQLBackupDirectory($project, $output).'/mysql.dmp.previous.gz', $output);
        $process->executeCommand('mv '.$filesystem->getMySQLBackupDirectory($project, $output).'/mysql.dmp.gz '.$filesystem->getMySQLBackupDirectory($project, $output).'/mysql.dmp.previous.gz', $output);
        $process->executeCommand("echo 'SET autocommit=0;' > ".$filesystem->getMySQLBackupDirectory($project, $output)."/mysql.dmp", $output);
		$process->executeCommand("echo 'SET unique_checks=0;' >> ".$filesystem->getMySQLBackupDirectory($project, $output)."/mysql.dmp", $output);
		$process->executeCommand("echo 'SET foreign_key_checks=0;' >> ".$filesystem->getMySQLBackupDirectory($project, $output)."/mysql.dmp", $output);
		$process->executeCommand("mysqldump --ignore-table=".$project->getMysqlUser().".sessions --skip-opt --add-drop-table --add-locks --create-options --disable-keys --single-transaction --skip-extended-insert --quick --set-charset -u ".$project->getMysqlUser()." -p".$project->getMysqlPassword()." ".$project->getMysqlUser()." >> ".$filesystem->getMySQLBackupDirectory($project, $output)."/mysql.dmp", $output);
		$process->executeCommand("echo 'COMMIT;' >> ".$filesystem->getMySQLBackupDirectory($project, $output)."/mysql.dmp", $output);
		$process->executeCommand("echo 'SET autocommit=1;' >> ".$filesystem->getMySQLBackupDirectory($project, $output)."/mysql.dmp", $output);
		$process->executeCommand("echo 'SET unique_checks=1;' >> ".$filesystem->getMySQLBackupDirectory($project, $output)."/mysql.dmp", $output);
		$process->executeCommand("echo 'SET foreign_key_checks=1;' >> ".$filesystem->getMySQLBackupDirectory($project, $output)."/mysql.dmp", $output);
        $process->executeCommand("gzip ".$filesystem->getMySQLBackupDirectory($project, $output)."/mysql.dmp -f", $output);
    }

    /**
     * @param \Cilex\Application $app
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return mixed
     */
    public function postBackup(Application $app, Project $project, OutputInterface $output)
    {
    }

    /**
     * @param \Cilex\Application $app
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return mixed
     */
    public function preRemove(Application $app, Project $project, OutputInterface $output)
    {
    }

    /**
     * @param \Cilex\Application $app
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return mixed
     */
    public function postRemove(Application $app, Project $project, OutputInterface $output)
    {
        $pdo = new PDO('mysql:host='.$project->getMysqlHost().';port='.$project->getMysqlPort().';dbname='.$project->getMysqlUser(), $project->getMysqlUser(), $project->getMysqlPassword());
        $pdo->exec("drop database " . $project->getMysqlUser());
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
