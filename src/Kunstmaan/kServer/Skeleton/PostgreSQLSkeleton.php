<?php
namespace Kunstmaan\kServer\Skeleton;

use Kunstmaan\kServer\Entity\PostgreSQLConfig;

use Kunstmaan\kServer\Entity\MysqlConfig;

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
use Kunstmaan\kServer\Helper\OutputUtil;

/**
 * PostgresQLSkeleton
 */
class PostgreSQLSkeleton extends AbstractSkeleton
{

    const NAME = "mysql";

    /**
     * @return string
     */
    public function getName()
    {
        return PostgreSQLSkeleton::NAME;
    }

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed
     */
    public function create(Application $app, Project $project, OutputInterface $output)
    {
        /** @var $filesystem FileSystemProvider */
        $filesystem = $app["filesystem"];
        $filesystem->createDirectory($project, $output, 'backup');

        /** @var $console \Symfony\Component\Console\Application */
        $console = $app['console'];
        /** @var $dialog DialogHelper */
        $dialog = $console->getHelperSet()->get('dialog');

        $postgresqlConfig = new PostgreSQLConfig();
        $postgresqlConfig->setUser($dialog->ask($output, '      <question>Enter the preferred PostgreSQL username: ['.$project->getName().'] </question>', $project->getName()));

        $pwgen = new PWGen();
        $password = $pwgen->generate();

        $postgresConfig->setPassword($dialog->ask($output, '      <question>Enter the preferred PostgreSQL password: ['.$password.'] </question>', $password));
        $postgresConfig->setHost($dialog->ask($output, '      <question>Enter the MySQL hostname: [localhost] </question>', 'localhost'));
        $postgresConfig->setPort($dialog->ask($output, '      <question>Enter the MySQL port: [3306] </question>', 3306));

        $project->setConfiguration(MySQLSkeleton::NAME, $mysqlConfig);

        {
            $permissionDefinition = new PermissionDefinition();
            $permissionDefinition->setName("backup-postgres");
            $permissionDefinition->setPath("/backup");
            $permissionDefinition->addAcl("-R -m u:postgres:r-X");
            $project->addPermissionDefinition($permissionDefinition);
        }
    }

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed
     */
    public function maintenance(Application $app, Project $project, OutputInterface $output)
    {
        /* @var $mysqlConfig MysqlConfig */
        $mysqlConfig = $project->getConfiguration(MySQLSkeleton::NAME);
        $host = $mysqlConfig->getMysqlHost();
        $port = $mysqlConfig->getMysqlPort();
        $user = $mysqlConfig->getMysqlUser();
        $password = $mysqlConfig->getMysqlPassword();
        try {
            $pdo = new PDO('mysql:host='.$host.';port='.$port.';dbname='.$user, $user, $password);
        } catch (PDOException $exLoginTest) {
            OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "Cannot connect as " . $user . ", let's test if the database exists (".$exLoginTest->getMessage().")");
            try {
                $pdo = new PDO('mysql:host='.$host.';port='.$port.';dbname='.$user, $app["config"]["mysql"]["rootuser"], $app["config"]["mysql"]["rootpassword"]);
            } catch (PDOException $exDBTest) {
                OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "Cannot connect to the " . $user . " database as the root user as well, let's create it. (".$exDBTest->getMessage().")");
                $pdo = new PDO('mysql:host='.$host.';port='.$port, $app["config"]["mysql"]["rootuser"], $app["config"]["mysql"]["rootpassword"]);
                $pdo->exec(OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "mysql", "create database " . $user . " CHARACTER SET utf8 COLLATE utf8_general_ci;"));
                $finder = new Finder();
                /** @var $filesystem FileSystemProvider */
                $filesystem = $app["filesystem"];
                $finder->files()->in($filesystem->getMySQLBackupDirectory($project, $output))->name("mysql.dmp.gz");
                if (sizeof(iterator_to_array($finder)) > 0) {
                    /** @var $process ProcessProvider */
                    $process = $app["process"];
                    $process->executeCommand('gzip -dc '.$filesystem->getMySQLBackupDirectory($project, $output).'/mysql.dmp.gz | mysql -h '.$host.' -P '.$port.' -u '.$app["config"]["mysql"]["rootuser"].' -p'.$app["config"]["mysql"]["rootpassword"].' '.$user, $output);
                    OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, 'MySQL database created based on ' . $filesystem->getMySQLBackupDirectory($project, $output));
                } else {
                    OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, 'Empty MySQL database created');
                }

            }
            $pdo->exec(OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "mysql", "GRANT ALL PRIVILEGES ON ".$user.".* TO ".$user."@localhost IDENTIFIED BY '".$password."'"));
            $pdo->exec(OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "mysql", "GRANT ALL PRIVILEGES ON ".$user.".* TO ".$user."@'%%' IDENTIFIED BY '".$password."'"));
        }
    }

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed
     */
    public function preBackup(Application $app, Project $project, OutputInterface $output)
    {
        /* @var $mysqlConfig MysqlConfig */
        $mysqlConfig = $project->getConfiguration(MySQLSkeleton::NAME);
        /* @var $process ProcessProvider */
        $process = $app["process"];
        /* @var $filesystem FileSystemProvider */
        $filesystem = $app["filesystem"];
        $process->executeCommand('rm -f '.$filesystem->getMySQLBackupDirectory($project, $output).'/mysql.dmp', $output);
        if (is_file($filesystem->getMySQLBackupDirectory($project, $output).'/mysql.dmp.gz')) {
            $process->executeCommand('rm -f '.$filesystem->getMySQLBackupDirectory($project, $output).'/mysql.dmp.previous.gz', $output);
            $process->executeCommand('mv '.$filesystem->getMySQLBackupDirectory($project, $output).'/mysql.dmp.gz '.$filesystem->getMySQLBackupDirectory($project, $output).'/mysql.dmp.previous.gz', $output);
        }
        $process->executeCommand("echo 'SET autocommit=0;' > ".$filesystem->getMySQLBackupDirectory($project, $output)."/mysql.dmp", $output);
        $process->executeCommand("echo 'SET unique_checks=0;' >> " . $filesystem->getMySQLBackupDirectory($project, $output) . "/mysql.dmp", $output);
        $process->executeCommand("echo 'SET foreign_key_checks=0;' >> " . $filesystem->getMySQLBackupDirectory($project, $output) . "/mysql.dmp", $output);
        $process->executeCommand("mysqldump --ignore-table=" . $mysqlConfig->getMysqlUser().".sessions --skip-opt --add-drop-table --add-locks --create-options --disable-keys --single-transaction --skip-extended-insert --quick --set-charset -u " . $mysqlConfig->getMysqlUser() . " -p".$mysqlConfig->getMysqlPassword() . " ".$mysqlConfig->getMysqlUser() . " >> " . $filesystem->getMySQLBackupDirectory($project, $output) . "/mysql.dmp", $output);
        $process->executeCommand("echo 'COMMIT;' >> " . $filesystem->getMySQLBackupDirectory($project, $output) . "/mysql.dmp", $output);
        $process->executeCommand("echo 'SET autocommit=1;' >> " . $filesystem->getMySQLBackupDirectory($project, $output) . "/mysql.dmp", $output);
        $process->executeCommand("echo 'SET unique_checks=1;' >> " . $filesystem->getMySQLBackupDirectory($project, $output) . "/mysql.dmp", $output);
        $process->executeCommand("echo 'SET foreign_key_checks=1;' >> " . $filesystem->getMySQLBackupDirectory($project, $output) . "/mysql.dmp", $output);
        $process->executeCommand("gzip " . $filesystem->getMySQLBackupDirectory($project, $output) . "/mysql.dmp -f", $output);
    }

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed
     */
    public function postBackup(Application $app, Project $project, OutputInterface $output)
    {
    }

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed
     */
    public function preRemove(Application $app, Project $project, OutputInterface $output)
    {
    }

    /**
     * @param Application     $app     The application
     * @param Project         $project The project
     * @param OutputInterface $output  The command output stream
     *
     * @return mixed
     */
    public function postRemove(Application $app, Project $project, OutputInterface $output)
    {
        /* @var $postgresqlConfig PostgreSQLConfig */
        $postgresqlConfig = $project->getConfiguration(PostgreSQLSkeleton::NAME);

        $pdo = new PDO('mysql:host='.$mysqlConfig->getHost().';port='.$mysqlConfig->getPort().';dbname='.$mysqlConfig->getUser(), $mysqlConfig->getUser(), $mysqlConfig->getPassword());
        $pdo->exec(OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "mysql", "drop database " . $mysqlConfig->getUser()));
    }

    /**
     * @param Project      $project The project
     * @param \ArrayObject $config  The configuration array
     */
    public function writeConfig(Project $project, \ArrayObject $config)
    {
        /* @var $postgresqlConfig PostgreSQLConfig */
        $postgresqlConfig = $project->getConfiguration(PostgreSQLSkeleton::NAME);
        if (!is_null($postgresqlConfig->getUser())) {
            $config["postgresql"]["user"] = $postgresqlConfig->getUser();
        }
        if (!is_null($postgresqlConfig->getPassword())) {
            $config["postgresql"]["password"] = $postgresqlConfig->getPassword();
        }
        if (!is_null($postgresqlConfig->getHost())) {
            $config["postgresql"]["host"] = $postgresqlConfig->getHost();
        }
        if (!is_null($postgresqlConfig->getPort())) {
            $config["postgresql"]["port"] = $postgresqlConfig->getPort();
        }
    }

    /**
     * @param Project      $project The project
     * @param \ArrayObject $config  The configuration array
     */
    public function loadConfig(Project $project, \ArrayObject $config)
    {
        $postgresqlConfig = new PostgreSQLConfig();
        if (isset($config["postgresql"]["user"])) {
            $postgresqlConfig->setUser($config["postgresql"]["user"]);
        }
        if (isset($config["postgresql"]["password"])) {
            $postgresqlConfig->setPassword($config["postgresql"]["password"]);
        }
        if (isset($config["postgresql"]["host"])) {
            $postgresqlConfig->setHost($config["postgresql"]["host"]);
        }
        if (isset($config["postgresql"]["port"])) {
            $postgresqlConfig->setPort($config["postgresql"]["port"]);
        }
        $project->setConfiguration(PostgreSQLSkeleton::NAME, $postgresqlConfig);
    }

}
