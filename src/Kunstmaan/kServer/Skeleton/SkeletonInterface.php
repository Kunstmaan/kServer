<?php
namespace Kunstmaan\kServer\Skeleton;


use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Application;
use Kunstmaan\kServer\Entity\Project;

interface SkeletonInterface
{

    /**
     * @return string
     */
    public function getName();

    /**
     * @param \Cilex\Application $app
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return mixed
     */
    public function create(Application $app, Project $project, OutputInterface $output);

    /**
     * @param \Cilex\Application $app
     * @param \Kunstmaan\kServer\Entity\Project $project
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return mixed
     */
    public function maintenance(Application $app, Project $project, OutputInterface $output);

}
