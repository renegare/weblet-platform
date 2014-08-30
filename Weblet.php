<?php

namespace Renegare\Weblet\Platform;

use Renegare\Weblet\Base\Weblet as BaseWeblet;
use Renegare\Soauth\OAuthControllerServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;

class Weblet extends BaseWeblet {

    public function enableSecurity() {
        $this['security.firewalls'] = [
            'auth' => [
                'pattern' => '^/auth',
                'stateless' => true],

            'oauth2' => [
                'pattern' => '^/',
                'soauth' => true]
        ];

        $this->doRegister(new ServiceControllerServiceProvider);
        $this->doRegister(new SecurityServiceProvider, ['security.firewalls']);
        $oauthProvider = new OAuthControllerServiceProvider;
        $this->doRegister($oauthProvider, ['soauth.client.provider.config', 'soauth.test']);
        $this->mount('/auth', $oauthProvider);
    }
}
