<?php

namespace WyriHaximus\React\Tests\AsyncInteropLoop;

use Interop\Async\Loop;
use React\EventLoop\LibEventLoop;
use WyriHaximus\React\AsyncInteropLoop\AsyncInteropLoop;
use WyriHaximus\React\AsyncInteropLoop\ReactDriverFactory;

class AsyncInteropLibEventLoopTest extends AbstractLoopTestCase
{
    public function createLoop()
    {
        $this->markTestSkipped();
        if (!function_exists('event_base_new')) {
            $this->markTestSkipped();
        }
        Loop::setFactory(ReactDriverFactory::createFactoryFromLoop(LibEventLoop::class));
        return new AsyncInteropLoop();
    }
}
