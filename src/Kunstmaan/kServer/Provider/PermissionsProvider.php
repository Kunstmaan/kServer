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
            if (PHP_OS == "Darwin"){
                $this->app["process"]->executeCommand('dscl . create /groups/'.$groupname, $output);
                $this->app["process"]->executeCommand('dscl . create /groups/'.$groupname. " name ". $groupname, $output);
                $this->app["process"]->executeCommand('dscl . create /groups/'.$groupname.' passwd "*"', $output);
            } else {
                $this->app["process"]->executeCommand('addgroup '.$groupname, $output);
            }
        }
    }

    private function isGroup($groupname, OutputInterface $output){
        if (PHP_OS == "Darwin"){
            return $this->app["process"]->executeCommand('dscl . -list /groups | grep ^'.$groupname.'$', $output, true);
        } else {
            return $this->app["process"]->executeCommand('cat /etc/group | egrep ^'.$groupname.':', $output, true);
        }
    }

    public function createUserIfNeeded($username, $groupname, OutputInterface $output){
        if (! $this->isUser($username, $output)){
            if (PHP_OS == "Darwin"){
			    $maxid = $this->app["process"]->executeCommand("dscl . list /Users UniqueID | awk '{print $2}' | sort -ug | tail -1");
			    $maxid = $maxid + 1;
			    $this->app["process"]->executeCommand('dscl . create /Users/'.$username, $output);
			    $this->app["process"]->executeCommand('dscl . create /Users/'.$username.' UserShell /bin/bash', $output);
			    $this->app["process"]->executeCommand('dscl . create /Users/'.$username.' NFSHomeDirectory /var/www/'.$username, $output);
			    $this->app["process"]->executeCommand('dscl . create /Users/'.$username.' PrimaryGroupID 20', $output);
			    $this->app["process"]->executeCommand('dscl . create /Users/'.$username.' UniqueID ' . $maxid, $output);
			    $this->app["process"]->executeCommand('dscl . append /Groups/'.$groupname.' GroupMembership '.$username, $output);
			    $this->app["process"]->executeCommand('defaults write /Library/Preferences/com.apple.loginwindow HiddenUsersList -array-add '.$username, $output);
            } else {
                $this->app["process"]->executeCommand('adduser --firstuid 1000 --lastuid 1999 --disabled-password --system --quiet --ingroup '.$groupname.' --home "/var/www/'.$username.'" --no-create-home --shell /bin/bash '.$username, $output);
            }
        }
    }

    private function isUser($username, OutputInterface $output){
        return $this->app["process"]->executeCommand('id '.$username, $output, true);
    }
}
