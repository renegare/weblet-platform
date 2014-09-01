<?php

namespace Renegare\Weblet\Platform;

use Renegare\Weblet\Base\Weblet as BaseWeblet;
use Renegare\Soauth\OAuthControllerServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;

class Weblet extends BaseWeblet {

    public function enableSecurity() {
        $this->addFirewall('healthcheck', [
                'pattern' => sprintf('^/%s', trim($this->getHealthCheckUri(), '/')),
                'stateless' => true]);
        $this->addFirewall('auth', [
                'pattern' => '^/auth',
                'stateless' => true]);
        $this->addFirewall('oauth2', [
                'pattern' => '^/',
                'soauth' => true]);

        $this->doRegister(new ServiceControllerServiceProvider);
        $this->doRegister(new SecurityServiceProvider, ['security.firewalls']);
        $oauthProvider = new OAuthControllerServiceProvider;
        $this->doRegister($oauthProvider, ['soauth.client.provider.config', 'soauth.test']);
        $this->mount('/auth', $oauthProvider);
    }

    public function addFirewall($name, $config) {
        $existingConfig = isset($this['security.firewalls'])? $this['security.firewalls'] : [];
        $this['security.firewalls'] = array_merge($existingConfig, [$name => $config]);
    }
}
