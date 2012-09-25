<?php
namespace Kunstmaan\kServer\Provider;

use Cilex\ServiceProviderInterface;
use Kunstmaan\kServer\Entity\Project;
use Symfony\Component\Finder\Finder;
use Cilex\Application;
use Kunstmaan\kServer\Provider\FileSystemProvider;
use Kunstmaan\kServer\Provider\ProcessProvider;
use Symfony\Component\Console\Output\OutputInterface;

class PermissionsProvider implements ServiceProviderInterface
{

    /**
     * @var Application
     */
    private $app;

    /**
     * Registers services on the given app.
     *
     * @param Application $app An Application instance
     */
    function register(Application $app)
    {
        $app['permission'] = $this;
        $this->app = $app;
    }

    /**
     * @param $groupname
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function createGroupIfNeeded($groupname, OutputInterface $output)
    {
        if (!$this->isGroup($groupname, $output)) {
            $process = $this->app["process"];
            if (PHP_OS == "Darwin") {
                $process->executeCommand('dscl . create /groups/' . $groupname, $output);
                $process->executeCommand('dscl . create /groups/' . $groupname . " name " . $groupname, $output);
                $process->executeCommand('dscl . create /groups/' . $groupname . ' passwd "*"', $output);
            } else {
                $process->executeCommand('addgroup ' . $groupname, $output);
            }
        }
    }

    /**
     * @param $groupname
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return mixed
     */
    private function isGroup($groupname, OutputInterface $output)
    {
        $process = $this->app["process"];
        if (PHP_OS == "Darwin") {
            return $process->executeCommand('dscl . -list /groups | grep ^' . $groupname . '$', $output, true);
        } else {
            return $process->executeCommand('cat /etc/group | egrep ^' . $groupname . ':', $output, true);
        }
    }

    /**
     * @param $username
     * @param $groupname
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function createUserIfNeeded($username, $groupname, OutputInterface $output)
    {
        if (!$this->isUser($username, $output)) {
            $process = $this->app["process"];
            if (PHP_OS == "Darwin") {
                $maxid = $process->executeCommand("dscl . list /Users UniqueID | awk '{print $2}' | sort -ug | tail -1");
                $maxid = $maxid + 1;
                $process->executeCommand('dscl . create /Users/' . $username, $output);
                $process->executeCommand('dscl . create /Users/' . $username . ' UserShell /bin/bash', $output);
                $process->executeCommand('dscl . create /Users/' . $username . ' NFSHomeDirectory /var/www/' . $username, $output);
                $process->executeCommand('dscl . create /Users/' . $username . ' PrimaryGroupID 20', $output);
                $process->executeCommand('dscl . create /Users/' . $username . ' UniqueID ' . $maxid, $output);
                $process->executeCommand('dscl . append /Groups/' . $groupname . ' GroupMembership ' . $username, $output);
                $process->executeCommand('defaults write /Library/Preferences/com.apple.loginwindow HiddenUsersList -array-add ' . $username, $output);
            } else {
                $process->executeCommand('adduser --firstuid 1000 --lastuid 1999 --disabled-password --system --quiet --ingroup ' . $groupname . ' --home "/var/www/' . $username . '" --no-create-home --shell /bin/bash ' . $username, $output);
            }
        }
    }

    /**
     * @param $username
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return mixed
     */
    private function isUser($username, OutputInterface $output)
    {
        $process = $this->app["process"];
        return $process->executeCommand('id ' . $username, $output, true);
    }

    /**
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function applyOwnership(Project $project, OutputInterface $output)
    {
        /** @var $process ProcessProvider */
        $process = $this->app["process"];
        /** @var $filesystem FileSystemProvider */
        $filesystem = $this->app['filesystem'];
        foreach ($project->getPermissionDefinitions() as $pd) {
            $process->executeCommand('chown ' . $pd->getOwnership() . ' ' . $filesystem->getProjectDirectory($project->getName()) . $pd->getPath(), $output);
        }
    }

    /**
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function applyPermissions(Project $project, OutputInterface $output)
    {
        /** @var $process ProcessProvider */
        $process = $this->app["process"];
        /** @var $filesystem FileSystemProvider */
        $filesystem = $this->app['filesystem'];
        if ($this->app["config"]["permissions"]["develmode"]) {
            $process->executeCommand('chmod -R 777 ' . $filesystem->getProjectDirectory($project->getName()), $output);
            $process->executeCommand('chmod -R 700 ' . $filesystem->getProjectDirectory($project->getName()) . '/.ssh/', $output);
        } else {
            foreach ($project->getPermissionDefinitions() as $pd) {
                foreach ($pd->getAcl() as $acl) {
                    $process->executeCommand('setfacl ' . $acl . ' ' . $filesystem->getProjectDirectory($project->getName()) . $pd->getPath(), $output);
                }
            }
        }
    }

    /**
     * @param $username
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function killProcesses($username, OutputInterface $output){
        /** @var $process ProcessProvider */
        $process = $this->app["process"];
        $process->executeCommand("su - ".$username." -c 'kill -9 -1'",$output, true);
    }

    /**
     * @param $username
     * @param $groupname
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function removeUser($username, $groupname, OutputInterface $output)
    {
        if ($this->isUser($username, $output)) {
            $process = $this->app["process"];
            if (PHP_OS == "Darwin") {
                $process->executeCommand('dscl . delete /Users/' . $username, $output);
                $process->executeCommand('dscl . delete /Groups/' . $groupname, $output);
            } else {
                $process->executeCommand('userdel ' . $username, $output);
            }
        }
    }
}