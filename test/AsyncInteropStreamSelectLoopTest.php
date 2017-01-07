<?php

namespace WyriHaximus\React\Tests\AsyncInteropLoop;

use AsyncInterop\Loop;
use React\EventLoop\StreamSelectLoop;
use React\Tests\EventLoop\AbstractLoopTest;
use WyriHaximus\React\AsyncInteropLoop\AsyncInteropLoop;
use WyriHaximus\React\AsyncInteropLoop\ReactDriverFactory;

class AsyncInteropStreamSelectLoopTest extends AbstractLoopTest
{
    public function createLoop()
    {
        Loop::setFactory(ReactDriverFactory::createFactoryFromLoop(StreamSelectLoop::class));
        return new AsyncInteropLoop();
    }
}
