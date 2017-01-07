<?php

namespace WyriHaximus\React\Tests\AsyncInteropLoop;

use AsyncInterop\Loop\Test;
use WyriHaximus\React\AsyncInteropLoop\ReactDriverFactory;

class ReactEventLoopTest extends Test
{
    public function getFactory()
    {
        return ReactDriverFactory::createFactory();
    }
}
