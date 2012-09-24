<?php
namespace Kunstmaan\kServer\Command;

use Symfony\Component\Console\Input\InputArgument;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Command\Command;

use Kunstmaan\kServer\Provider\FileSystemProvider;

class NewProjectCommand extends Command
{


    protected function configure()
    {
        $this
            ->setName('newproject')
            ->setDescription('Create a new kServer project')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the project. All lowercase, no spaces or special characters. Keep it short, yet descriptive');


    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $filesystem FileSystemProvider */
        $filesystem = $this->getService('filesystem');
        $projectname = $input->getArgument('name');
        if ($filesystem->projectExists($projectname)){
            throw new RuntimeException("A project with name $projectname already exists!");
        }
        $output->writeln("<info> ---> Creating project $projectname</info>");
        $filesystem->createProjectDirectory($projectname);





        $text = 'Hello';
        $name = $projectname;
        if ($name) {
            $text .= ' '.$name;
        }



        $output->writeln($text);
    }
}

/**
 * globalConfig = smllib.projectinformation.getBaseInformation()
p = Information(None)
p.mergeWith(globalConfig)



p.queryUser("project.name")

p.queryUser("project.dir")
p.queryUser("project.user")
p.queryUser("project.group")


p.bindXML("%s/conf/config.xml" % (p["project.dir"]))
skeletonName = "base"
skc = SkeletonCopier(skeletonName, p["config.skeletonsdir"]+"/" + skeletonName , p["project.dir"])
skc.performAction(p)
p.save()
actionok()

action("Sending log to admin")
smllib.postman.getThePostman().send("Project Creation: %s" % p['project.name'])
actionok()

D("Now start applying skeletons to your project with applyskel.py.", 0)

smllib.aspn.unlock()
 */