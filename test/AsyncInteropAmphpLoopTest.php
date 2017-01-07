<?php

namespace WyriHaximus\React\Tests\AsyncInteropLoop;

use Amp\Loop\LoopFactory;
use AsyncInterop\Loop;
use WyriHaximus\React\AsyncInteropLoop\AsyncInteropLoop;

class AsyncInteropAmphpLoopTest extends AbstractLoopTestCase
{
    public function createLoop()
    {
        Loop::setFactory(new LoopFactory());
        return new AsyncInteropLoop();
    }
}
