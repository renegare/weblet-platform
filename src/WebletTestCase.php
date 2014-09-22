<?php

namespace Renegare\Weblet\Platform;

use Renegare\Weblet\Base\WebletTestCase as BaseWebletTestCase;
use Renegare\Weblet\Base\Weblet as BaseWeblet;
use Renegare\Soauth\CredentialsInterface;
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

    public function createAuthenticatedClient(array $credentialAttrs = [], $createdTime = null, array $server = [], Weblet $app = null) {
        $app = $app? $app : $this->getApplication();

        $credentials = $this->createCredentials($credentialAttrs);
        $this->createUser($credentials, $credentialAttrs, $app);
        $this->saveCredentials($credentials, $createdTime, $app);

        $server = array_merge(['HTTP_X_ACCESS_CODE' => $credentials->getAccessCode()], $server);
        $client = $this->createClient($server, $app);
        return $client;
    }

    abstract public function createUser(CredentialsInterface $credentials, $attrs, Weblet $app);
}
