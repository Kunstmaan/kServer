<?php
namespace Kunstmaan\kServer\Skeleton;


use Symfony\Component\Console\Output\OutputInterface;
use Kunstmaan\kServer\Entity\PermissionDefinition;
use Cilex\Application;
use Kunstmaan\kServer\Entity\Project;
use Kunstmaan\kServer\Provider\FileSystemProvider;
use Kunstmaan\kServer\Provider\PermissionsProvider;



class BaseSkeleton implements SkeletonInterface
{

    public function getName(){
        return "base";
    }

    /**
     *     permissions:
    - root:
    path:       "/"
    ownership:  "-R $superuser.$group"
    acl:
    - "-R -m user::rw-"
    - "-R -m group::---"
    - "-R -m other::---"
     */

    public function create(Application $app, Project $project, OutputInterface $output)
    {
        /** @var $filesystem FileSystemProvider */
        $filesystem = $app["filesystem"];
        $filesystem->createProjectConfigDirectory($project, $output);
        $project->addDependency($this);
        $permissionDefinition = new PermissionDefinition();
        $permissionDefinition->setName("root");
        $permissionDefinition->setPath("/");
        $permissionDefinition->setOwnership("-R root." . $project->getName());
        $permissionDefinition->addAcl("-R -m user::rw-");
        $permissionDefinition->addAcl("-R -m group::---");
        $permissionDefinition->addAcl("-R -m other::---");
        $project->addPermissionDefinition($permissionDefinition);
    }

    public function permissions(Application $app, Project $project, OutputInterface $output){
        /** @var $permission PermissionsProvider */
        $permission = $app["permission"];
        $permission->createGroupIfNeeded($project->getName(), $output);
        $permission->createUserIfNeeded($project->getName(), $project->getName() , $output);
        $permission->applyOwnership($project, $output);
    }

}

/**
class PermissionsPlugin (MaintenancePlugin):
def __init__(self):
pass

### begin * MaintenancePlugin implementation ###
def getAbout(self):
return "creates user if needed and fixes permissions"

def canSkip(self):
return True

def getPluginName(self):
return "permissions fixer"

def doOnProject(self, information):
self.createGroupIfNeeded(information["project.group"],information)
self.createUserIfNeeded(information["project.user"],information["project.dir"],information["project.group"],information)

ownershipdict = XMLDictionary("%s/conf/ownership.xml" % information["project.dir"])
ownershipdict.toSave=[""]

if (not "/" in ownershipdict):
raise "no ownership information"

ownershipdict.save()

xmldict = XMLDictionary("%s/conf/permissions.xml" % information["project.dir"])
xmldict.toSave=[""]
if (not "/" in xmldict):
raise "no permissions information"

self.fixOwnership(information,ownershipdict)
self.applyPermissions(information,xmldict)
xmldict.save()

def doPostProjects(self):
pass

def doPreProjects(self):
pass

def checkPreconditions(self, information):
information.require("project.dir")
information.require("project.user")
information.require("config.wwwuser")
information.require("project.group")

### end * MaintenancePlugin implementation ###

def fixOwnership(self, information, ownershipdict):
"""
runs through the ownership.xml settings
and applies them to the found folders
if a folder specified in ownership.xml is not found, no error or warning is raised
"""
replacer = information.getReplacer()
for fileORIG in getSorted(ownershipdict.keys()):
file = replacer.replace(fileORIG)
if (file == '/' or self.exists(file, information['project.dir'])):
ownerORIG = ownershipdict[fileORIG]
owner = replacer.replace(ownerORIG)
if (smllib.platform.getPlatform()[0] == "Darwin"):
owner = owner.replace(".", ":")

#print "chown %s %s/%s" % (owner, information["project.dir"], file)
CMD("chown %s %s/%s" % (owner, information["project.dir"], file))

def applyPermissions(self, information, permissionsdict):
"""
runs through the permissions.xml settings
and applies them to the found folders
if a folder specified in permissions.xml is not found,
no error or warning is raised
"""
if ("config.develmode" in information.keys() and information["config.develmode"] == "true"):
CMD("chmod -R 777 %s/" % (information['project.dir']))
CMD("chmod -R 700 %s/.ssh/" % (information['project.dir']))
#warn("DEVEL MODE")
return

replacer = information.getReplacer()
for folderORIG in getSorted(permissionsdict.keys()):
folder = replacer.replace(folderORIG)
if (type(permissionsdict[folderORIG]) == list):
for permissionsORIG in permissionsdict[folderORIG]:
permissions = replacer.replace(permissionsORIG)
#			if (folder == "/" or folder.lstrip("/") in os.listdir(information['project.dir'])):
#			if (folder == "/" or self.exists(folder, information['project.dir'])):
totalfolder = "%s/%s" % (information["project.dir"], folder)
if (self.fexists(totalfolder)):
#warn("setfacl %s %s/%s" % (permissions, information["project.dir"], folder))
CMD("setfacl %s %s/%s" % (permissions, information["project.dir"], folder))
#else:
#warn("fix permissions: %s does not exist" % totalfolder)

def fexists(self,file):
try:
import os
os.stat(file)
return True
except OSError:
return False

def	exists(self, file, dir):
"""
returns true if a given file is found in a given dir
returns false otherwise
"""
try:
import os
os.stat(dir+file)
return True
except OSError:
#smllib.shell.warn("Did not found the dir \"%s\" defined in permissions.xml or ownership.xml, so I will search for \"%s\" (slow)" % ((dir+file), file.split('/')[-1]) );
found = CMDGET("find %s -name \"%s\"" % (dir, file.split('/')[-1]))
if (found != ''):
for result in found.split("\n"):
if (result.replace("/", "") == (dir+file).replace("/", "")):
return True
else:
return False

def isGroup(self, group):
"""
returns true if the given group name is an existing group
on the system. false otherwise
"""
if (smllib.platform.getPlatform()[0] == "Darwin"):
ret = CMDX("dscl . -list /groups | grep ^%s$" % group)
if (ret == 1):
return False
return True
else:
ret = CMDX("cat /etc/group | egrep ^%s: >/dev/null || exit 1" % group)
if (ret == 0):
return True
return False

def isUser(self,user):
ret = CMDX("id %s >/dev/null 2>&1  || exit 1" % user)
if (ret == 0):
return True
return False

def createUser(self,user,homedir,group,information):
D("creating user %s (group %s) with homedir %s" % (user,group,homedir), 3)
if (smllib.platform.getPlatform()[0] == "Darwin"):
maxid = CMDGET("dscl . list /Users UniqueID | awk '{print $2}' | sort -ug | tail -1")
maxid = int(maxid) + 1
CMD("dscl . create /Users/%s" % user)
CMD("dscl . create /Users/%s UserShell /bin/bash" % user)
CMD("dscl . create /Users/%s NFSHomeDirectory %s" % (user, homedir))
CMD("dscl . create /Users/%s PrimaryGroupID 20" % user)
CMD("dscl . create /Users/%s UniqueID %s" % (user, maxid))
CMD("dscl . append /Groups/%s GroupMembership %s" % (group, user))
CMD("defaults write /Library/Preferences/com.apple.loginwindow HiddenUsersList -array-add %s" % user)
else:
CMD("adduser --firstuid 1000 --lastuid 1999 --disabled-password --system --quiet --ingroup %s --home \"%s\" --no-create-home --shell /bin/bash %s" % (group, homedir, user))

def createGroupIfNeeded(self,group,information):
if (self.isGroup(group)): return True
D("creating group %s" % group, 3)
if (smllib.platform.getPlatform()[0] == "Darwin"):
CMD("dscl . create /groups/%s" % group)
CMD("dscl . create /groups/%s name %s" % (group,group))
CMD("dscl . create /groups/%s passwd \"*\"" % group)
else:
CMD("addgroup %s" % group)

def createUserIfNeeded(self,user,homedir,group,information):
if (self.isUser(user)): return True
self.createUser(user,homedir,group,information)

def getPlugin():
return PermissionsPlugin()
 */