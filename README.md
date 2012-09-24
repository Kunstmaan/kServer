# kServer

## What's kServer?

kServer is the missing link between provisioning scripts (puppet, chef, shell scripts etc) and deployment scripts
(capistrano) for environments that are not completely single purpose (like most startup websites or big custom projects)
and are also not generic and fixed enough to use virtual hosting scripts (ISPConfig, Plesk, etc).

The provisioning scripts will setup the server, install all packages, configure all the services (mySQL, SOLR, apache,
php-fpm). kServer will be responsible for creating the project structure, mySQL user, apache vhosts, system user, etc,
provides a frame for running backups and prepare everything so you can run the deployment scripts to put your code online.

## How does kServer work?

kServer is a script that allows you to create a project and apply skeletons to it. These skeletons are separate pieces
of functionality, with dependencies, that together create the hosting environment for your project. More info about the
different skeletons is available below.

kServer creates a ```kserver``` folder in the root of your project with all the configuration files for these different
services.

TODO: add more specific information