<?php

namespace Renegare\Weblet\Platform\Test\Functional;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Renegare\Weblet\Platform\Test\WebletTestCase;
use Renegare\Weblet\Base\Weblet;

class HealthCheckTest extends WebletTestCase {

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

        $app['soauth.user.provider.config'] = [
            'test@example.com' => ['password' => $app['security.encoder.digest']->encodePassword('Password123', ''), 'roles' => ['ROLE_USER'], 'enabled' => true]
        ];

        $renderMock = $this->getMock('Renegare\Soauth\RendererInterface');
        $renderMock->expects($this->any())->method('renderSignInForm')
            ->will($this->returnCallback(function($data){
                extract($data);
                $tmpl = <<<'EOF'
<form method="post">
    <input type="text" name="username" value="%s"/>
    <input type="password" name="password" />
    <input type="hidden" name="redirect_uri" value="%s" />
    <input type="hidden" name="client_id" value="%s" />
    <button type="submit">Sign-in</button>
</form>
EOF
;
                $username = isset($username)? $username : '';
                return sprintf($tmpl, $username, $redirect_uri, $client_id);
            }));
        $app['soauth.renderer'] = $renderMock;

        $app->get('/test-endpoint', function() use ($app){
            return $app['security']->getToken()->getUsername();
        });
    }

    public function testFlow() {
        // start authenticatication
        $client = $this->createClient();
        $client->followRedirects(true);
        $crawler = $client->request('GET', '/_healthcheck');
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());    }
}
