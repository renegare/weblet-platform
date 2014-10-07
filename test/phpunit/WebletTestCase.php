<?php

namespace Renegare\Weblet\Platform\Test;

use Renegare\Soauth\Access\Access;
use Renegare\Weblet\Platform\Weblet;
use Renegare\Weblet\Platform\WebletTestCase as PlatformWebletTestCase;

class WebletTestCase extends PlatformWebletTestCase {

    public function createUser(Access $access, array $attrs, Weblet $app) {}
}
