<?php
namespace Kunstmaan\kServer\Provider;

use Cilex\ServiceProviderInterface;
use Kunstmaan\kServer\Entity\Project;
use Symfony\Component\Finder\Finder;
use Cilex\Application;
use Symfony\Component\Console\Output\OutputInterface;

class PermissionsProvider implements ServiceProviderInterface
{

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

    public function createGroupIfNeeded($groupname, OutputInterface $output){
        if (! $this->isGroup($groupname, $output)){
            $process = $this->app["process"];
            if (PHP_OS == "Darwin"){
                $process->executeCommand('dscl . create /groups/'.$groupname, $output);
                $process->executeCommand('dscl . create /groups/'.$groupname. " name ". $groupname, $output);
                $process->executeCommand('dscl . create /groups/'.$groupname.' passwd "*"', $output);
            } else {
                $process->executeCommand('addgroup '.$groupname, $output);
            }
        }
    }

    private function isGroup($groupname, OutputInterface $output){
        $process = $this->app["process"];
        if (PHP_OS == "Darwin"){
            return $process->executeCommand('dscl . -list /groups | grep ^'.$groupname.'$', $output, true);
        } else {
            return $process->executeCommand('cat /etc/group | egrep ^'.$groupname.':', $output, true);
        }
    }

    public function createUserIfNeeded($username, $groupname, OutputInterface $output){
        if (! $this->isUser($username, $output)){
            $process = $this->app["process"];
            if (PHP_OS == "Darwin"){
			    $maxid = $process->executeCommand("dscl . list /Users UniqueID | awk '{print $2}' | sort -ug | tail -1");
			    $maxid = $maxid + 1;
			    $process->executeCommand('dscl . create /Users/'.$username, $output);
			    $process->executeCommand('dscl . create /Users/'.$username.' UserShell /bin/bash', $output);
			    $process->executeCommand('dscl . create /Users/'.$username.' NFSHomeDirectory /var/www/'.$username, $output);
			    $process->executeCommand('dscl . create /Users/'.$username.' PrimaryGroupID 20', $output);
			    $process->executeCommand('dscl . create /Users/'.$username.' UniqueID ' . $maxid, $output);
			    $process->executeCommand('dscl . append /Groups/'.$groupname.' GroupMembership '.$username, $output);
			    $process->executeCommand('defaults write /Library/Preferences/com.apple.loginwindow HiddenUsersList -array-add '.$username, $output);
            } else {
                $process->executeCommand('adduser --firstuid 1000 --lastuid 1999 --disabled-password --system --quiet --ingroup '.$groupname.' --home "/var/www/'.$username.'" --no-create-home --shell /bin/bash '.$username, $output);
            }
        }
    }

    private function isUser($username, OutputInterface $output){
        $process = $this->app["process"];
        return $process->executeCommand('id '.$username, $output, true);
    }
}
