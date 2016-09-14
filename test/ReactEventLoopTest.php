<?php

namespace WyriHaximus\React\Tests\AsyncInteropLoop;

use Interop\Async\Loop\Test;
use React\EventLoop\StreamSelectLoop;
use WyriHaximus\React\AsyncInteropLoop\ReactDriverFactory;

class ReactEventLoopTest extends Test
{
    public function getFactory()
    {
        return ReactDriverFactory::createFactoryFromLoop(StreamSelectLoop::class);
    }
}
