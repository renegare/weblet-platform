<?php

namespace Renegare\Weblet\Platform;

use Renegare\Weblet\Base\WebletTestCase as BaseWebletTestCase;
use Renegare\Weblet\Base\Weblet as BaseWeblet;
use Renegare\Soauth\Access\Access;
use Renegare\Soauth\SoauthTestCaseTrait;

abstract class WebletTestCase extends BaseWebletTestCase {
    use SoauthTestCaseTrait;

    /**
     * {@inheritdoc}
     */
    public function createApplication() {
        $app = new Weblet(['debug' => true]);
        set_exception_handler(null);
        return $app;
    }

    public function createAuthorizedClient(array $accessAttrs = [], $createdTime = null, array $server = [], Weblet $app = null) {
        $app = $app? $app : $this->getApplication();

        $access = $this->createAuthorizationCodeAccess($accessAttrs);
        $this->createUser($access, $accessAttrs, $app);
        $this->saveAccess($app, $access, $createdTime);

        $server = array_merge(['HTTP_Authorization' => 'Bearer ' . $access->getAccessToken()], $server);
        $client = $this->createClient($server, $app);
        return $client;
    }

    public function createRegisteredClient(array $accessAttrs = [], $createdTime = null, array $server = [], Weblet $app = null) {
        $app = $app? $app : $this->getApplication();

        $access = $this->createClientCredentialsAccess($accessAttrs);
        $this->saveAccess($app, $access, $createdTime);

        $server = array_merge(['HTTP_Authorization' => 'Bearer ' . $access->getAccessToken()], $server);
        $client = $this->createClient($server, $app);
        return $client;
    }

    abstract public function createUser(Access $access, array $attrs, Weblet $app);
}
