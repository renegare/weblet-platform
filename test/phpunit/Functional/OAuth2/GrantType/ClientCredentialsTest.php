<?php

namespace Renegare\Weblet\Platform\Test\Functional\OAuth2\GrantType;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Renegare\Weblet\Platform\Test\WebletTestCase;
use Renegare\Weblet\Base\Weblet;

class ClientCredentialsTest extends WebletTestCase {

    public function configureApplication(Weblet $app) {
        parent::configureApplication($app);
        $app->enableSecurity();

        $app['soauth.test'] = true;
        $app['soauth.client.provider.config'] = [
            '1' => [
                'name' => 'Client Application',
                'domain' => 'e4mpl3.ngrok.com',
                'active' => 'true',
                'secret' => 'ch3ng4Th15!']
        ];

        $app->get('/test-endpoint', function() use ($app){
            return $app['security']->getToken()->getUsername();
        });
    }

    public function testClientCredentialsFlow() {
        $app = $this->getApplication();
        $clientInfo = $app['soauth.client.provider.config'][1];

        // exchange for access code
        $client = $this->createClient();
        $client->request('POST', '/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => 1,
            'client_secret' => $clientInfo['secret']
        ]);

        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $access = json_decode($response->getContent(), true);

        // ensure access_code allows access to resource
        $client = $this->createRegisteredClient([
            'access_token' => $access['access_token']
        ]);
        $client->request('GET', '/test-endpoint');
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('1', $response->getContent());
    }
}
