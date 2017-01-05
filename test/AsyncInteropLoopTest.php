<?php

namespace WyriHaximus\React\Tests\AsyncInteropLoop;

use Interop\Async\Loop;
use WyriHaximus\React\AsyncInteropLoop\AsyncInteropLoop;
use WyriHaximus\React\AsyncInteropLoop\ReactDriverFactory;

class AsyncInteropLoopTest extends AbstractLoopTestCase
{
    public function createLoop()
    {
        Loop::setFactory(ReactDriverFactory::createFactory());
        return new AsyncInteropLoop();
    }
}
