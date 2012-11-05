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

## Dependencies

* Vagrant: install the gem using `gem install vagrant` or download from http://vagrantup.com/
* Vangrant-hostmaster: execute: `vagrant gem install vagrant-hostmaster` (https://github.com/mosaicxm/vagrant-hostmaster)

## Running kServer

### Running under Vagrant

#### Installing this website on your development machine using kServer

```bash
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
```

#### Migrating an existing Symfony website, example with KunstmaanSandbox

```bash
git clone git@github.com:Kunstmaan/KunstmaanSandbox.git
cd KunstmaanSandbox
composer install
cat <<EOF >> .gitignore
.gitmodules
.vagrant
Vagrantfile
kserver
EOF
git submodule add -f git@github.com:Kunstmaan/kServer.git kserver
cd kserver
composer install
cd ..
ln -sf kserver/project/Vagrantfile Vagrantfile
vagrant up # You will see some errors at the end, since maintenance will not work before the creation of the config files
vagrant ssh
```

In your vagrant box:

```bash
sudo -i
ks
./kserver new kunstmaansandbox --migrate
./kserver apply kunstmaansandbox php
./kserver apply kunstmaansandbox mysql
```

Then rsync the database dump and uploaded files to your project

```bash
rsync -rltD -vh --progress --compress <yourusername>@<theserver>:/home/projects/<oldprojectname>/backup/* /var/www/kunstmaansandbox/backup/
rsync -rltD -vh --progress --compress <yourusername>@<theserver>:/home/projects/<oldprojectname>/data/shared/web/uploads /var/www/kunstmaansandbox/current/web/

```


### Running on a server

Since a physical server install does not have a provisioning system, we need to do some things by hand. SSH into your server, become root and run:

```bash
sudo -i
cd /opt/
apt-get install git
git clone https://github.com/Kunstmaan/kServer.git
cd kServer
bash provisioning/installer /opt/kServer MYSQLROOTPASSWD newrelickey
cp config.yml.dist config.yml
vim config.yml # set the hostname
cd /opt/kServer
composer install
```
