<?php

namespace WyriHaximus\React\Tests\AsyncInteropLoop;

use AsyncInterop\Loop\Test;
use React\EventLoop\StreamSelectLoop;
use WyriHaximus\React\AsyncInteropLoop\ReactDriverFactory;

class ReactStreamSelectLoopTest extends Test
{
    public function getFactory()
    {
        return ReactDriverFactory::createFactoryFromLoop(StreamSelectLoop::class);
    }
}
