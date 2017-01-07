<?php

namespace WyriHaximus\React\Tests\AsyncInteropLoop;

use AsyncInterop\Loop\Test;
use React\EventLoop\LibEvLoop;
use WyriHaximus\React\AsyncInteropLoop\ReactDriverFactory;

class ReactLibEvLoopTest extends Test
{
    public function getFactory()
    {
        if (!class_exists('libev\EventLoop', false)) {
            $this->markTestSkipped();
        }
        return ReactDriverFactory::createFactoryFromLoop(LibEvLoop::class);
    }
}
