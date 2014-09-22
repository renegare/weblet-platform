<?php

namespace Renegare\Weblet\Platform\Test;

use Renegare\Soauth\CredentialsInterface;
use Renegare\Weblet\Platform\Weblet;
use Renegare\Weblet\Platform\WebletTestCase as PlatformWebletTestCase;

class WebletTestCase extends PlatformWebletTestCase {

    public function createUser(CredentialsInterface $credentials, $attrs, Weblet $app) {}
}
