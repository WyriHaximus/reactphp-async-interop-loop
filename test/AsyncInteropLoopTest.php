<?php

namespace WyriHaximus\React\Tests\AsyncInteropLoop;

use Interop\Async\Loop;
use React\Tests\EventLoop\AbstractLoopTest;
use WyriHaximus\React\AsyncInteropLoop\AsyncInteropLoop;
use WyriHaximus\React\AsyncInteropLoop\ReactDriverFactory;

class AsyncInteropLoopTest extends AbstractLoopTest
{
    public function createLoop()
    {
        Loop::setFactory(ReactDriverFactory::createFactory());
        return new AsyncInteropLoop();
    }
}
