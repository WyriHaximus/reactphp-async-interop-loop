<?php

namespace WyriHaximus\React\Tests\AsyncInteropLoop;

use Interop\Async\Loop;
use React\EventLoop\LibEvLoop;
use React\Tests\EventLoop\AbstractLoopTest;
use WyriHaximus\React\AsyncInteropLoop\AsyncInteropLoop;
use WyriHaximus\React\AsyncInteropLoop\ReactDriverFactory;

class AsyncInteropLibEvLoopTest extends AbstractLoopTest
{
    public function createLoop()
    {
        if (!class_exists('libev\EventLoop', false)) {
            $this->markTestSkipped();
        }
        Loop::setFactory(ReactDriverFactory::createFactoryFromLoop(LibEvLoop::class));
        return new AsyncInteropLoop();
    }
}
