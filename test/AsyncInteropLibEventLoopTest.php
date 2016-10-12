<?php

namespace WyriHaximus\React\Tests\AsyncInteropLoop;

use Interop\Async\Loop;
use React\EventLoop\LibEventLoop;
use React\Tests\EventLoop\AbstractLoopTest;
use WyriHaximus\React\AsyncInteropLoop\AsyncInteropLoop;
use WyriHaximus\React\AsyncInteropLoop\ReactDriverFactory;

class AsyncInteropLibEventLoopTest extends AbstractLoopTest
{
    public function createLoop()
    {
        if (!function_exists('event_base_new')) {
            $this->markTestSkipped();
        }
        Loop::setFactory(ReactDriverFactory::createFactoryFromLoop(LibEventLoop::class));
        return new AsyncInteropLoop();
    }
}
