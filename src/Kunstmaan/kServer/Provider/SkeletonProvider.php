<?php

namespace Kunstmaan\kServer\Provider;

use Cilex\ServiceProviderInterface;
use Kunstmaan\kServer\Skeleton\SkeletonInterface;
use Kunstmaan\kServer\Entity\Project;
use Symfony\Component\Console\Output\OutputInterface;
use RuntimeException;
use Cilex\Application;

class SkeletonProvider  implements ServiceProviderInterface
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
        $app['skeleton'] = $this;
        $this->app = $app;
    }

    function applySkeleton(Project $project, SkeletonInterface $skeleton, OutputInterface $output){
        $output->writeln("<comment>      > Applying ". get_class($skeleton) . " to ".$project->getName()." </comment>");
        $skeleton->create($this->app, $project, $output);
    }

}


/*
class SkeletonCopier:
	def __init__(self, skelname, skeldir, destdir):
		self.skeldir = skeldir
		self.skelname = skelname
		self.destdir = destdir
		self.skelfiles = skeldir + "/files"
		self.skeltemplates = skeldir + "/templates"
		self.skelplugins = skeldir + "/plugins/maintenance"

	def _copyFiles(self):
		fs = [self.skelfiles + "/" + x for x in os.listdir(self.skelfiles)]
		for f in fs:
			if shouldIgnore(f):
			    continue
			CMD("rsync -a --cvs-exclude --exclude '.emptyDir' --exclude '.cvsignore'  %s %s" % (f.rstrip('/'), self.destdir.rstrip('/')))

	def _copySkel(self, replacer):
		fs = [x for x in  os.listdir(self.skeltemplates)]
		for f in fs:
			src = self.skeltemplates + "/" + f
			dest = self.destdir + "/" + f
			processTemplateEntry(src, dest, replacer)

#	def _copyPlugins(self):
#		CMD("mkdir -p %s/conf" % self.destdir)
#		CMD("mkdir -p %s/conf/plugins" % self.destdir)
#		fs = [x for x in os.listdir(self.skelplugins) ]
#		for f in fs:
#			src = self.skelplugins + "/" + f
#			if (not shouldIgnore(src)):
#				dest = self.destdir + "/conf/plugins/" + self.skelname + "_" + f;
#				CMD("ln -s %s %s" % (src,dest))

	def _execPlugins(self, info):
		plugindir = self.skeldir + "/plugins/creation"
		l = Loader(plugindir)
		plugins = l.loadPlugins()
		[D("PLUGIN %s (%s)" % (plugin.getPluginName(), plugin.getAbout())) for plugin in plugins]
		[plugin.performAction(info) for plugin in plugins]

	def performAction(self, info):
		replacer = info.getReplacer()
		self._copyFiles()
		self._copySkel(replacer)
		#self._copyPlugins()
		self._execPlugins(info)
		x = []
		if ("project.skeletons" in info.keys()):
			x = info["project.skeletons"]
		x.append(self.skelname)

		info['project.skeletons'] = x
 */