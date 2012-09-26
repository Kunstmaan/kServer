# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant::Config.run do |config|
  config.vm.box = "kunstmaan-precise64"
  config.vm.box_url = "http://vagrant.kunstmaan.be/kunstmaan-precise64.box"

  # Boot with a GUI so you can see the screen. (Default is headless)
  #config.vm.boot_mode = :gui

  projectname="kserver"

  config.vm.share_folder("kserver", "/opt/kServer", ".", :nfs => true)
  config.vm.network :hostonly, "33.33.33.33"
  config.ssh.forward_agent = true

  config.vm.network :hostonly, "33.33.33.33"
  config.vm.host_name = "#{projectname}.dev.kunstmaan.be"
  config.hosts.aliases = "www.#{projectname}.dev.kunstmaan.be"

  config.vm.provision :shell, :path => "provisioning/installer", :args => "/vagrant vagrant"
end
