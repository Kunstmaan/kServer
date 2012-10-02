<?php
namespace Kunstmaan\kServer\Provider;

use Cilex\ServiceProviderInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Cilex\Application;
use Symfony\Component\Console\Helper\DialogHelper;


class DialogProvider implements ServiceProviderInterface
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
        $app['dialog'] = $this;
        $this->app = $app;
    }

    /**
     * @param $argumentname
     * @param $message
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     * @throws RuntimeException
     */
    public function askFor($argumentname, $message, InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument($argumentname);
        if (is_null($name)) {
            /** @var $dialog DialogHelper */
            $dialog = $this->app['console']->getHelperSet()->get('dialog');
            $name = $dialog->ask($output, '<question>'.$message.': </question>');
        }
        if (is_null($name)) {
            throw new RuntimeException("A $argumentname is required, what am I, psychic?");
        }
        return $name;
    }

    public function askConfirmation(OutputInterface $output, $question, $default = true)
    {
        /** @var $dialog DialogHelper */
        $dialog = $this->app['console']->getHelperSet()->get('dialog');
        $dialog->askConfirmation($this->output, $question, $default);
    }
}
