<?php
namespace Kunstmaan\kServer\Skeleton;

use Kunstmaan\kServer\Entity\MySQLConfig;

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
 * MySQLSkeleton
 */
class MySQLSkeleton extends AbstractSkeleton
{

    const NAME = "mysql";

    /**
     * @return string
     */
    public function getName()
    {
        return MySQLSkeleton::NAME;
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

        $mysqlConfig = new MySQLConfig();
        $mysqlConfig->setUser($dialog->ask($output, '      <question>Enter the preferred MySQL username: ['.$project->getName().'] </question>', $project->getName()));

        $pwgen = new PWGen();
        $password = $pwgen->generate();

        $mysqlConfig->setPassword($dialog->ask($output, '      <question>Enter the preferred MySQL password: ['.$password.'] </question>', $password));
        $mysqlConfig->setHost($dialog->ask($output, '      <question>Enter the MySQL hostname: [localhost] </question>', 'localhost'));
        $mysqlConfig->setPort($dialog->ask($output, '      <question>Enter the MySQL port: [3306] </question>', 3306));

        $project->setConfiguration(MySQLSkeleton::NAME, $mysqlConfig);
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
        /* @var $filesystem FileSystemProvider */
        $filesystem = $app["filesystem"];
        $backupDir = $filesystem->getDirectory($project, $output, 'backup');
        /* @var $mysqlConfig MySQLonfig */
        $mysqlConfig = $project->getConfiguration(MySQLSkeleton::NAME);
        $host = $mysqlConfig->getHost();
        $port = $mysqlConfig->getPort();
        $user = $mysqlConfig->getUser();
        $password = $mysqlConfig->getPassword();
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
                $finder->files()->in($backupDir)->name("mysql.dmp.gz");
                if (sizeof(iterator_to_array($finder)) > 0) {
                    /** @var $process ProcessProvider */
                    $process = $app["process"];
                    $process->executeCommand('gzip -dc '.$backupDir.'/mysql.dmp.gz | mysql -h '.$host.' -P '.$port.' -u '.$app["config"]["mysql"]["rootuser"].' -p'.$app["config"]["mysql"]["rootpassword"].' '.$user, $output);
                    OutputUtil::log($output, OutputInterface::VERBOSITY_NORMAL, 'MySQL database created based on ' . $backupDir);
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
        /* @var $filesystem FileSystemProvider */
        $filesystem = $app["filesystem"];
        $backupDir = $filesystem->getDirectory($project, $output, 'backup');
        /* @var $mysqlConfig MySQLConfig */
        $mysqlConfig = $project->getConfiguration(MySQLSkeleton::NAME);
        /* @var $process ProcessProvider */
        $process = $app["process"];
        /* @var $filesystem FileSystemProvider */
        $filesystem = $app["filesystem"];
        $process->executeCommand('rm -f '.$backupDir.'/mysql.dmp', $output);
        if (is_file($backupDir.'/mysql.dmp.gz')) {
            $process->executeCommand('rm -f '.$backupDir.'/mysql.dmp.previous.gz', $output);
            $process->executeCommand('mv '.$backupDir.'/mysql.dmp.gz '.$backupDir.'/mysql.dmp.previous.gz', $output);
        }
        $process->executeCommand("echo 'SET autocommit=0;' > ".$backupDir."/mysql.dmp", $output);
        $process->executeCommand("echo 'SET unique_checks=0;' >> " . $backupDir . "/mysql.dmp", $output);
        $process->executeCommand("echo 'SET foreign_key_checks=0;' >> " . $backupDir . "/mysql.dmp", $output);
        $process->executeCommand("mysqldump --ignore-table=" . $mysqlConfig->getUser().".sessions --skip-opt --add-drop-table --add-locks --create-options --disable-keys --single-transaction --skip-extended-insert --quick --set-charset -u " . $mysqlConfig->getUser() . " -p".$mysqlConfig->getPassword() . " ".$mysqlConfig->getUser() . " >> " . $backupDir . "/mysql.dmp", $output);
        $process->executeCommand("echo 'COMMIT;' >> " . $backupDir . "/mysql.dmp", $output);
        $process->executeCommand("echo 'SET autocommit=1;' >> " . $backupDir . "/mysql.dmp", $output);
        $process->executeCommand("echo 'SET unique_checks=1;' >> " . $backupDir . "/mysql.dmp", $output);
        $process->executeCommand("echo 'SET foreign_key_checks=1;' >> " . $backupDir . "/mysql.dmp", $output);
        $process->executeCommand("gzip " . $backupDir . "/mysql.dmp -f", $output);
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
        /* @var $mysqlConfig MySQLConfig */
        $mysqlConfig = $project->getConfiguration(MySQLSkeleton::NAME);

        $pdo = new PDO('mysql:host='.$mysqlConfig->getHost().';port='.$mysqlConfig->getPort().';dbname='.$mysqlConfig->getUser(), $mysqlConfig->getUser(), $mysqlConfig->getPassword());
        $pdo->exec(OutputUtil::log($output, OutputInterface::VERBOSITY_VERBOSE, "mysql", "drop database " . $mysqlConfig->getUser()));
    }

    /**
     * @param Project      $project The project
     * @param \ArrayObject $config  The configuration array
     */
    public function writeConfig(Project $project, \ArrayObject $config)
    {
        $mysqlConfig = $project->getConfiguration(MySQLSkeleton::NAME);
        if (!is_null($mysqlConfig->getHost())) {
            $config["mysql"]["host"] = $mysqlConfig->getHost();
        }
        if (!is_null($mysqlConfig->getPort())) {
            $config["mysql"]["port"] = $mysqlConfig->getPort();
        }
        if (!is_null($mysqlConfig->getUser())) {
            $config["mysql"]["user"] = $mysqlConfig->getUser();
        }
        if (!is_null($mysqlConfig->getPassword())) {
            $config["mysql"]["password"] = $mysqlConfig->getPassword();
        }
    }

    /**
     * @param Project      $project The project
     * @param \ArrayObject $config  The configuration array
     */
    public function loadConfig(Project $project, \ArrayObject $config)
    {
        $mysqlConfig = new MySQLConfig();
        if (isset($config["mysql"]["host"])) {
            $mysqlConfig->setHost($config["mysql"]["host"]);
        }
        if (isset($config["mysql"]["port"])) {
            $mysqlConfig->setPort($config["mysql"]["port"]);
        }
        if (isset($config["mysql"]["user"])) {
            $mysqlConfig->setUser($config["mysql"]["user"]);
        }
        if (isset($config["mysql"]["password"])) {
            $mysqlConfig->setPassword($config["mysql"]["password"]);
        }

        $project->setConfiguration(MySQLSkeleton::NAME, $mysqlConfig);
    }

    /**
     * @param \Cilex\Application $app
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return string[]
     */
    public function dependsOn(Application $app, Project $project, OutputInterface $output)
    {
        return array(
            "base"
        );
    }

}
