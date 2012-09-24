<?php

namespace Kunstmaan\kServer\Provider;

use Cilex\ServiceProviderInterface;
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


}
