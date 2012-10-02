<?php
namespace Kunstmaan\kServer\Provider;

use Cilex\ServiceProviderInterface;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Cilex\Application;
use Symfony\Component\Console\Helper\DialogHelper;

class DialogProvider extends AbstractProvider
{

    /**
     * @var Application
     */
    private $app;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var DialogHelper
     */
    private $dialog;

    function __construct($output)
    {
        $this->output = $output;
    }

    /**
     * Registers services on the given app.
     *
     * @param Application $app An Application instance
     */
    function register(Application $app)
    {
        $app['dialog'] = $this;
        $this->app = $app;
        $this->dialog = $this->app['console']->getHelperSet()->get('dialog');
    }

    /**
     * @param string $argumentname
     * @param string $message
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @return string
     * @throws \RuntimeException
     */
    public function askFor($argumentname, $message, InputInterface $input)
    {
        $name = $input->getArgument($argumentname);
        if (is_null($name)) {
            $name = $this->dialog->ask($this->output, '<question>' . $message . ': </question>');
        }
        if (is_null($name)) {
            throw new RuntimeException("A $argumentname is required, what am I, psychic?");
        }
        return $name;
    }

    public function askConfirmation($question, $default = true)
    {
        $this->dialog->askConfirmation($this->output, $question, $default);
    }
}
