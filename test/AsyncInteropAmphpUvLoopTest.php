<?php

namespace WyriHaximus\React\Tests\AsyncInteropLoop;

use Amp\Loop\UvLoop;
use Interop\Async\Loop;
use React\Tests\EventLoop\AbstractLoopTest;
use WyriHaximus\React\AsyncInteropLoop\AsyncInteropLoop;

/** @requires PHP 7 */
class AsyncInteropAmphpUvLoopTest extends AbstractLoopTest
{
    public function createLoop()
    {
        if (!extension_loaded('uv')) {
            $this->markTestSkipped();
        }

        Loop::setFactory(new class implements Loop\DriverFactory {
            public function create()
            {
                return new UvLoop();
            }
        });
        return new AsyncInteropLoop();
    }
}
