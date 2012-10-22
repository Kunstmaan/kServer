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

## Running kServer

### Running under Vagrant

#### Installing this website on your development machine using kServer

```
git clone git@github.com:Kunstmaan/KunstmaanBundles.git
cd KunstmaanBundles
./param decode
composer install
git submodule add -f git@github.com:Kunstmaan/kServer.git kserver
cd kserver
composer install
cd ..
ln -sf kserver/project/Vagrantfile Vagrantfile
vagrant up
open http://kunstmaanbundles.dev.kunstmaan.be
```

#### Migrating an existing website, example with the Sandbox

```
git clone git@github.com:Kunstmaan/KunstmaanSandbox.git
cd KunstmaanSandbox
# ./param decode (for the Kunstmaan projects)
cp app/config/parameters.yml.dist app/config/parameters.yml
composer install
git submodule add -f git@github.com:Kunstmaan/kServer.git kserver
cd kserver
composer install
cd ..
ln -sf kserver/project/Vagrantfile Vagrantfile
cp -r kserver/project/kconfig .
sed 's@NAME.NAME@kunstmaansandbox.kunstmaansandbox@' kconfig/project.yml > /tmp/project.yml
sed 's@NAME@kunstmaansandbox@' /tmp/project.yml > kconfig/project.yml
vagrant up
vagrant ssh
sudo -i
ks
./kserver apply kunstmaansandbox php
./kserver apply kunstmaansandbox mysql
```

### Running on a server

Since a physical server install does not have a provisioning system, we need to do some things by hand. SSH into your server, become root and run:

```bash
to to
```
