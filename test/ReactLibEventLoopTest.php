<?php

namespace WyriHaximus\React\Tests\AsyncInteropLoop;

use Interop\Async\Loop\Test;
use React\EventLoop\LibEventLoop;
use WyriHaximus\React\AsyncInteropLoop\ReactDriverFactory;

class ReactLibEventLoopTest extends Test
{
    public function getFactory()
    {
        if (!function_exists('event_base_new')) {
            $this->markTestSkipped();
        }
        return ReactDriverFactory::createFactoryFromLoop(LibEventLoop::class);
    }
}
