# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant::Config.run do |config|
  config.vm.box = "official-precise64"
  config.vm.box_url = "http://files.vagrantup.com/precise64.box"

  # Boot with a GUI so you can see the screen. (Default is headless)
  # config.vm.boot_mode = :gui

  config.ssh.forward_agent = true
  config.vm.network :hostonly, "33.33.33.33"

  config.vm.customize ["modifyvm", :id, "--memory", 1024]

  config.vm.share_folder("kserver", "/opt/kServer", ".", :nfs => true)

  config.vm.provision :shell, :path => "provisioning/installer", :args => "/vagrant vagrant newrelickey"
end
