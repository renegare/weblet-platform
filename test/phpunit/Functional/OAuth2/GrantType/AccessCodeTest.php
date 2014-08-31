<?php

namespace Renegare\Weblet\Platform\Test\Functional\OAuth2\GrantType;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Renegare\Weblet\Platform\Test\WebletTestCase;
use Renegare\Weblet\Base\Weblet;

class AccessCodeTest extends WebletTestCase {

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
        $app = $this->getApplication();
        $clientInfo = $app['soauth.client.provider.config'][1];

        // ensure no access
        $client = $this->createClient();
        $client->request('GET', '/test-endpoint');
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());

        // start authenticatication
        $client = $this->createClient();
        $crawler = $client->request('GET', '/auth/', [
            'redirect_uri' => 'http://e4mpl3.ngrok.com/cb',
            'client_id' => 1
        ]);
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $form = $crawler->selectButton('Sign-in')->form([
            'username' => 'test@example.com',
            'password' => 'Password123'
        ]);
        $client->submit($form);
        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $redirectTargetUrl = $response->getTargetUrl();
        $this->assertContains('http://e4mpl3.ngrok.com/cb?code=', $redirectTargetUrl);
        $authCode = explode('?code=', $redirectTargetUrl)[1];

        // exchange for access code
        $client = $this->createClient(['HTTP_X_CLIENT_SECRET' => $clientInfo['secret']]);
        $client->request('POST', '/auth/access/', [], [], [], json_encode(['code' => $authCode]));
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $credentials = json_decode($response->getContent(), true);

        // ensure access_code allows access to resource
        $client = $this->createAuthenticatedClient([
            'access_code' => $credentials['access_code']
        ]);
        $client->request('GET', '/test-endpoint');
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('test@example.com', $response->getContent());
    }
}
