<?php

namespace Renegare\Weblet\Platform;

use Renegare\Weblet\Base\WebletTestCase as BaseWebletTestCase;
use Renegare\Weblet\Base\Weblet as BaseWeblet;
use Renegare\Soauth\Credentials;
use Renegare\Soauth\SoauthTestCaseTrait;

class WebletTestCase extends BaseWebletTestCase {
    use SoauthTestCaseTrait;

    /**
     * {@inheritdoc}
     */
    public function createApplication() {
        $app = new Weblet(['debug' => true]);
        set_exception_handler(null);
        return $app;
    }

    public function createAuthenticatedClient(array $credentialAttrs = [], $createdTime = null, array $server = [], Weblet $app = null) {
        $app = $app? $app : $this->getApplication();

        $credentials = $this->createCredentials($credentialAttrs);
        $this->saveCredentials($credentials, $createdTime, $app);

        $server = array_merge(['HTTP_X_ACCESS_CODE' => $credentials->getAccessCode()], $server);
        $client = $this->createClient($server, $app);
        return $client;
    }
}
