<?php
namespace Kunstmaan\kServer\Skeleton;


use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Application;
use Kunstmaan\kServer\Entity\Project;

interface CreationInterface
{

    public function create(Application $app, Project $project, OutputInterface $output);

}
