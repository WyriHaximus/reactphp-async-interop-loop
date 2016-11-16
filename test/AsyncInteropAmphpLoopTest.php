<?php

namespace WyriHaximus\React\Tests\AsyncInteropLoop;

use Amp\Loop\LoopFactory;
use Interop\Async\Loop;
use React\Tests\EventLoop\AbstractLoopTest;
use WyriHaximus\React\AsyncInteropLoop\AsyncInteropLoop;

class AsyncInteropAmphpLoopTest extends AbstractLoopTest
{
    public function createLoop()
    {
        Loop::setFactory(new LoopFactory());
        return new AsyncInteropLoop();
    }
}
