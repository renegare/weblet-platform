<?php

namespace Renegare\Weblet\Platform\Test;

use Renegare\Weblet\Base\WebletTestCase as WTC;
use Renegare\Weblet\Base\Weblet as BaseWeblet;
use Renegare\Weblet\Platform\Weblet as PlatformWeblet;

class WebletTestCase extends WTC {

    public function getApplication() {
        return $this->app? $this->app : $this->createApplication();
    }
    public function getService($name) {
        return $this->getApplication()[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function createApplication() {
        $app = new PlatformWeblet;
        $this->configureApplication($app);
        return $app;
    }

    public function configureApplication(BaseWeblet $app) {
        $app['debug'] = true;
        $app['exception_handler']->disable();
        set_exception_handler(null);
    }
}
