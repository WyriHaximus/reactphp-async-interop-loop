<?php

namespace WyriHaximus\React\Tests\AsyncInteropLoop;

use AsyncInterop\Loop;
use React\EventLoop\LibEvLoop;
use WyriHaximus\React\AsyncInteropLoop\AsyncInteropLoop;
use WyriHaximus\React\AsyncInteropLoop\ReactDriverFactory;

class AsyncInteropLibEvLoopTest extends AbstractLoopTestCase
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
