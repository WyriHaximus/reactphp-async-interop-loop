<?php

namespace WyriHaximus\React\Tests\AsyncInteropLoop;

use AsyncInterop\Loop\Test;
use React\EventLoop\ExtEventLoop;
use WyriHaximus\React\AsyncInteropLoop\ReactDriverFactory;

class ReactExtEventLoopTest extends Test
{
    public function getFactory()
    {
        $this->markTestSkipped();
        if (!class_exists('EventBase', false)) {
            $this->markTestSkipped();
        }
        return ReactDriverFactory::createFactoryFromLoop(ExtEventLoop::class);
    }
}
