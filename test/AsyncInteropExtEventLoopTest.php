<?php

namespace WyriHaximus\React\Tests\AsyncInteropLoop;

use Interop\Async\Loop;
use React\EventLoop\ExtEventLoop;
use WyriHaximus\React\AsyncInteropLoop\AsyncInteropLoop;
use WyriHaximus\React\AsyncInteropLoop\ReactDriverFactory;

class AsyncInteropExtEventLoopTest extends AbstractLoopTestCase
{
    public function createLoop()
    {
        $this->markTestSkipped();
        if (!class_exists('EventBase', false)) {
            $this->markTestSkipped();
        }
        Loop::setFactory(ReactDriverFactory::createFactoryFromLoop(ExtEventLoop::class));
        return new AsyncInteropLoop();
    }
}
