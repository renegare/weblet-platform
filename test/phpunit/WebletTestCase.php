<?php

namespace Renegare\Weblet\Platform\Test;

use Renegare\Weblet\Base\WebletTestCase as WTC;
use Renegare\Weblet\Base\Weblet as BaseWeblet;
use Renegare\Weblet\Platform\Weblet as PlatformWeblet;
use Renegare\Soauth\Credentials;
use Renegare\Soauth\SoauthTestCaseTrait;

class WebletTestCase extends WTC {
    use SoauthTestCaseTrait;

    protected $mockLogger;

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
        $app['logger'] = $this->getMockLogger();
        set_exception_handler(null);
    }


    public function getMockLogger() {
        if(!$this->mockLogger) {
            $this->mockLogger = $this->getMock('Psr\Log\LoggerInterface');
        }

        return $this->mockLogger;
    }

    public function createAuthenticatedClient(array $credentialAttrs = [], $createdTime = null, array $server = [], WebApplication $app = null) {
        $app = $app? $app : $this->getApplication();

        $credentials = $this->createCredentials($credentialAttrs);
        $this->saveCredentials($credentials, $createdTime, $app);

        $server = array_merge(['HTTP_X_ACCESS_CODE' => $credentials->getAccessCode()], $server);
        $client = $this->createClient($server, $app);
        return $client;
    }
}
